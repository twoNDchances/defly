from django.conf import settings

from app.bases.models import User


class PermissionService:
    PERMISSION_MODEL = "Defender"

    @staticmethod
    def resolve_action_from_method(method: str) -> str | None:
        normalized_method = method.strip().lower()
        method_action_registry = {
            str(getattr(settings, "SERVER_METHOD_DEPLOY", "post"))
            .strip()
            .lower(): "deploy",
            str(getattr(settings, "SERVER_METHOD_FOLLOW", "get"))
            .strip()
            .lower(): "follow",
            str(getattr(settings, "SERVER_METHOD_CANCEL", "delete"))
            .strip()
            .lower(): "cancel",
        }
        return method_action_registry.get(normalized_method)

    @staticmethod
    def resolve_user_by_email(email: str) -> User | None:
        normalized_email = email.strip().lower()
        if not normalized_email:
            return None
        return User.objects.filter(email__iexact=normalized_email).first()

    @staticmethod
    def user_is_active_and_verified(user: User) -> bool:
        return bool(user.is_verified) and bool(user.is_activated)

    @staticmethod
    def user_has_action_permission(*, user: User, action: str) -> bool:
        direct_permission_exists = user.permissions.filter(
            applied_for=PermissionService.PERMISSION_MODEL,
            action__in=("all", action),
        ).exists()
        if direct_permission_exists:
            return True

        grouped_permission_exists = user.groups.filter(
            permissions__applied_for=PermissionService.PERMISSION_MODEL,
            permissions__action__in=("all", action),
        ).exists()
        return grouped_permission_exists
