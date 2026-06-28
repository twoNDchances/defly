from json import dumps, loads
from unittest.mock import AsyncMock, patch
from uuid import uuid4

from django.core.exceptions import ImproperlyConfigured, PermissionDenied
from django.test import RequestFactory, SimpleTestCase, override_settings

from app.assistant.services.ai import AssistantService
from app.assistant.services.permissions import ResourcePermissionService
from app.assistant.views.mai import ChatView
from app.bases.exceptions import AssistantServiceError


class ChatViewTests(SimpleTestCase):
    async def test_returns_ai_response_for_conversation(self):
        conversation_id = str(uuid4())
        messages = [{"role": "user", "content": "Hello"}]
        request = RequestFactory().get(
            f"/api/v1/assistant/{conversation_id}",
        )
        user = object()
        request.executor_user = user

        with (
            patch.object(
                AssistantService,
                "load_messages",
                new=AsyncMock(return_value=messages),
            ) as load_messages,
            patch.object(
                AssistantService,
                "chat",
                new=AsyncMock(return_value="Hello from AI"),
            ) as chat,
            patch.object(
                ResourcePermissionService,
                "validate_messages",
            ) as validate_messages,
        ):
            response = await ChatView.as_view()(
                request, conservation_id=conversation_id
            )

        self.assertEqual(200, response.status_code)
        self.assertEqual(
            {"role": "assistant", "content": "Hello from AI"},
            loads(response.content)["message"],
        )
        load_messages.assert_awaited_once_with(conversation_id)
        validate_messages.assert_awaited_once_with(messages=messages, user=user)
        chat.assert_awaited_once_with(messages, user=user)

    async def test_rejects_unauthorized_attached_resources(self):
        messages = [
            {
                "role": "user",
                "content": "Review this",
                "resources": [{"type": "label", "id": "resource-id"}],
            }
        ]
        conversation_id = str(uuid4())
        request = RequestFactory().get(
            f"/api/v1/assistant/{conversation_id}",
        )
        request.executor_user = object()

        with (
            patch.object(
                AssistantService,
                "load_messages",
                new=AsyncMock(return_value=messages),
            ),
            patch.object(
                ResourcePermissionService,
                "validate_messages",
                side_effect=PermissionDenied,
            ),
            patch.object(AssistantService, "chat", new=AsyncMock()) as chat,
        ):
            response = await ChatView.as_view()(
                request, conservation_id=conversation_id
            )

        self.assertEqual(403, response.status_code)
        self.assertEqual(
            {"detail": "Forbidden: attached resource permission denied."},
            loads(response.content),
        )
        chat.assert_not_awaited()

    async def test_validates_conversation_id_and_messages(self):
        missing_id = await ChatView.as_view()(RequestFactory().get("/api/v1/assistant"))
        self.assertEqual(400, missing_id.status_code)

        conversation_id = str(uuid4())
        with patch.object(
            AssistantService,
            "load_messages",
            new=AsyncMock(return_value=[]),
        ):
            missing_messages = await ChatView.as_view()(
                RequestFactory().get(
                    f"/api/v1/assistant/{conversation_id}",
                ),
                conservation_id=conversation_id,
            )
        self.assertEqual(404, missing_messages.status_code)

    @override_settings(SERVER_METHOD_ASSISTANT="post")
    async def test_uses_path_uuid_for_non_get_method(self):
        conversation_id = str(uuid4())
        messages = [{"role": "user", "content": "Hello"}]

        with (
            patch.object(
                AssistantService,
                "load_messages",
                new=AsyncMock(return_value=messages),
            ) as load_messages,
            patch.object(
                AssistantService,
                "chat",
                new=AsyncMock(return_value="Reply"),
            ),
        ):
            response = await ChatView.as_view()(
                RequestFactory().post(
                    f"/api/v1/assistant/{conversation_id}",
                    data=dumps({"id": str(uuid4())}),
                    content_type="application/json",
                ),
                conservation_id=conversation_id,
            )

        self.assertEqual(200, response.status_code)
        load_messages.assert_awaited_once_with(conversation_id)

    @override_settings(SERVER_METHOD_ASSISTANT="post")
    async def test_rejects_unconfigured_method(self):
        conversation_id = str(uuid4())
        response = await ChatView.as_view()(
            RequestFactory().get(f"/api/v1/assistant/{conversation_id}"),
            conservation_id=conversation_id,
        )

        self.assertEqual(405, response.status_code)
        self.assertEqual("POST", response.headers["Allow"])

    async def test_reports_configuration_and_provider_errors(self):
        conversation_id = str(uuid4())
        request = RequestFactory().get(
            f"/api/v1/assistant/{conversation_id}",
        )
        messages = [{"role": "user", "content": "Hello"}]

        for exception, expected_status in (
            (ImproperlyConfigured("missing key"), 503),
            (AssistantServiceError("provider failed"), 502),
        ):
            with self.subTest(expected_status=expected_status):
                with (
                    patch.object(
                        AssistantService,
                        "load_messages",
                        new=AsyncMock(return_value=messages),
                    ),
                    patch.object(
                        AssistantService,
                        "chat",
                        new=AsyncMock(side_effect=exception),
                    ),
                    patch("app.assistant.views.mai.logger.exception") as log_exception,
                ):
                    response = await ChatView.as_view()(
                        request,
                        conservation_id=conversation_id,
                    )

                self.assertEqual(expected_status, response.status_code)
                if expected_status == 502:
                    log_exception.assert_called_once_with("AI assistant request failed")
                else:
                    log_exception.assert_not_called()
