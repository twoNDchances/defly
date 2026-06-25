from django.conf import settings
from django.http import JsonResponse

from app.bases.middlewares import ServerHelperMiddleware
from app.bases.services.permissions import PermissionService
from app.deployments.services.permissions import DefenderPermissionService


class ServerPermissionMiddleware(ServerHelperMiddleware):
    @staticmethod
    def _is_deployment_request(request) -> bool:
        prefix = str(getattr(settings, "SERVER_PATH_PREFIX", "api/v1")).strip("/")
        deployment = str(
            getattr(settings, "SERVER_PATH_DEPLOYMENT", "deployments")
        ).strip("/")
        deployment_path = "/".join(
            segment for segment in (prefix, deployment) if segment
        )
        request_path = request.path_info.strip("/")

        return request_path == deployment_path or request_path.startswith(
            f"{deployment_path}/"
        )

    def __init__(self, get_response):
        self.get_response = get_response

    def __call__(self, request):
        if not self._is_deployment_request(request):
            return self.get_response(request)

        action = DefenderPermissionService.resolve_action_from_method(request.method)
        if action is None:
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
        request.executor_user = user
        request.defender_action = action

        return self.get_response(request)
