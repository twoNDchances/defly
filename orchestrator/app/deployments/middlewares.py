from django.conf import settings
from django.http import JsonResponse

from app.bases.middlewares import ServerHelperMiddleware
from app.bases.services.permissions import PermissionService


class ServerPermissionMiddleware(ServerHelperMiddleware):
    @staticmethod
    def _resolve_header_meta_key(header_name: str) -> str:
        normalized_header_name = header_name.strip().upper().replace("-", "_")
        return f"HTTP_{normalized_header_name}"

    def __init__(self, get_response):
        self.get_response = get_response

    def __call__(self, request):
        action = PermissionService.resolve_action_from_method(request.method)
        if action is None:
            return self.get_response(request)

        email_header_key = str(
            getattr(settings, "SERVER_EMAIL_HEADER_KEY", "X-Executor")
        ).strip()
        email_header_meta_key = self._resolve_header_meta_key(email_header_key)
        user_email = request.META.get(email_header_meta_key, "").strip().lower()
        if not user_email:
            return JsonResponse(
                {"detail": "Forbidden: missing user email header."},
                status=403,
            )

        user = PermissionService.resolve_user_by_email(user_email)
        if not PermissionService.can(
            user=user,
            model=PermissionService.PERMISSION_MODEL,
            action=action,
        ):
            return JsonResponse(
                {"detail": "Forbidden: user does not have permission."},
                status=403,
            )

        return self.get_response(request)
