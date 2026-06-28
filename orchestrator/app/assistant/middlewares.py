from django.conf import settings
from django.http import JsonResponse

from app.bases.middlewares import ServerHelperMiddleware
from app.bases.services.permissions import PermissionService


class ServerPermissionMiddleware(ServerHelperMiddleware):
    PERMISSION_MODEL = "Conservation"
    PERMISSION_ACTION = "chat"

    @staticmethod
    def _is_assistant_request(request) -> bool:
        prefix = str(getattr(settings, "SERVER_PATH_PREFIX", "api/v1")).strip("/")
        assistant = str(getattr(settings, "SERVER_PATH_ASSISTANT", "assistant")).strip(
            "/"
        )
        assistant_path = "/".join(segment for segment in (prefix, assistant) if segment)
        request_path = request.path_info.strip("/")

        return request_path == assistant_path or request_path.startswith(
            f"{assistant_path}/"
        )

    def __init__(self, get_response):
        self.get_response = get_response

    def __call__(self, request):
        if not self._is_assistant_request(request):
            return self.get_response(request)

        email_header_key = str(
            getattr(settings, "SERVER_EMAIL_HEADER_KEY", "X-Executor")
        ).strip()
        email_header_meta_key = self.resolve_header_meta_key(email_header_key)
        user_email = request.META.get(email_header_meta_key, "").strip().lower()
        if not user_email:
            return JsonResponse(
                {"detail": "Forbidden: missing user email header."},
                status=403,
            )

        user = PermissionService.resolve_user_by_email(user_email)
        if not PermissionService.can(
            user=user,
            model=self.PERMISSION_MODEL,
            action=self.PERMISSION_ACTION,
        ):
            return JsonResponse(
                {"detail": "Forbidden: user does not have permission."},
                status=403,
            )

        request.executor_user = user

        return self.get_response(request)
