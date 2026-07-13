from django.conf import settings
from django.core.exceptions import PermissionDenied
from django.db.models import Q
from django.utils import timezone

from app.bases.models import User
from app.bases.services.permissions import PermissionService
from app.deployments.models import Guard


class DefenderPermissionService:
    PERMISSION_MODEL = "Defender"
    ACTION_METHOD_SETTINGS = {
        "deploy": "SERVER_METHOD_DEPLOY",
        "follow": "SERVER_METHOD_FOLLOW",
        "cancel": "SERVER_METHOD_CANCEL",
    }

    @classmethod
    def resolve_action_from_method(cls, method: str) -> str | None:
        normalized_method = method.strip().lower()

        for action, setting_name in cls.ACTION_METHOD_SETTINGS.items():
            configured_method = str(getattr(settings, setting_name)).strip().lower()
            if normalized_method == configured_method:
                return action

        return None

    @classmethod
    async def validate_action(
        cls,
        *,
        user: User | None,
        action: str,
        defender_id=None,
    ) -> None:
        if action not in cls.ACTION_METHOD_SETTINGS:
            raise PermissionDenied("Unsupported Defender action.")

        if not await PermissionService.acan(
            user=user,
            model=cls.PERMISSION_MODEL,
            action=action,
        ):
            raise PermissionDenied("Defender action permission denied.")

        if defender_id is not None and not await cls.user_can_operate_defender(
            user=user,
            defender_id=defender_id,
        ):
            raise PermissionDenied("Defender guard permission denied.")

    @classmethod
    async def user_can_operate_defender(cls, *, user: User | None, defender_id) -> bool:
        guarded = await Guard.objects.filter(defenders__id=defender_id).aexists()
        if not guarded:
            return True

        if user is None:
            return False

        return (
            await Guard.objects.filter(
                defenders__id=defender_id,
                users__id=user.id,
            )
            .filter(Q(expired_at__isnull=True) | Q(expired_at__gt=timezone.now()))
            .aexists()
        )
