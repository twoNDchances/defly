from django.conf import settings
from django.core.exceptions import PermissionDenied

from app.bases.models import User
from app.bases.services.permissions import PermissionService


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
    async def validate_action(cls, *, user: User | None, action: str) -> None:
        if action not in cls.ACTION_METHOD_SETTINGS:
            raise PermissionDenied("Unsupported Defender action.")

        if not await PermissionService.acan(
            user=user,
            model=cls.PERMISSION_MODEL,
            action=action,
        ):
            raise PermissionDenied("Defender action permission denied.")
