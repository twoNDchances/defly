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
    def check_permission(*, user: User, model: str, action: str) -> bool:
        applied_for = model.split(".")[-1]

        has_direct_permission = user.permissions.filter(
            applied_for=applied_for,
            action=action,
        ).exists()
        if has_direct_permission:
            return True

        return user.groups.filter(
            permissions__applied_for=applied_for,
            permissions__action=action,
        ).exists()

    @staticmethod
    def can(*, user: User | None, model: str, action: str) -> bool:
        if user is None:
            return False
        if not PermissionService.user_is_active_and_verified(user):
            return False
        if bool(user.is_root) or PermissionService.check_permission(
            user=user,
            model=model,
            action="all",
        ):
            return True

        return PermissionService.check_permission(
            user=user,
            model=model,
            action=action,
        )
