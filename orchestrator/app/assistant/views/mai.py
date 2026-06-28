from logging import getLogger

from django.conf import settings
from django.core.exceptions import ImproperlyConfigured, PermissionDenied
from django.http import HttpRequest, JsonResponse

from app.assistant.services.ai import AssistantService
from app.assistant.services.permissions import ResourcePermissionService
from app.bases.exceptions import AssistantServiceError
from app.bases.views import ConfiguredMethodView

logger = getLogger(__name__)


class ChatView(ConfiguredMethodView):
    method_handler_names = {
        "SERVER_METHOD_ASSISTANT": "_handle_chat",
    }

    async def _handle_chat(self, request: HttpRequest, conservation_id=None):
        conservation_id = str(conservation_id or "").strip()
        if not conservation_id:
            return JsonResponse(
                {"detail": "The conversation uuid is required."},
                status=400,
            )

        messages = await AssistantService.load_messages(conservation_id)
        if not messages:
            return JsonResponse(
                {"detail": "Conversation messages not found."}, status=404
            )

        try:
            await ResourcePermissionService.validate_messages(
                messages=messages,
                user=getattr(request, "executor_user", None),
            )
        except PermissionDenied:
            return JsonResponse(
                {"detail": "Forbidden: attached resource permission denied."},
                status=403,
            )

        try:
            content = await AssistantService.chat(
                messages,
                user=getattr(request, "executor_user", None),
            )
        except ImproperlyConfigured as exception:
            return JsonResponse({"detail": str(exception)}, status=503)
        except AssistantServiceError:
            logger.exception("AI assistant request failed")
            return JsonResponse(
                {"detail": "The AI provider could not complete the request."},
                status=502,
            )

        return JsonResponse(
            {
                "message": {"role": "assistant", "content": content},
                "model": settings.AI_MODEL,
            }
        )
