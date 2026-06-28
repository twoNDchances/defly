from json import loads
from types import SimpleNamespace
from unittest.mock import Mock, patch

from django.http import JsonResponse
from django.test import RequestFactory, SimpleTestCase, override_settings

from app.assistant.middlewares import ServerPermissionMiddleware
from app.bases.services.permissions import PermissionService


class AssistantPermissionMiddlewareTests(SimpleTestCase):
    def setUp(self):
        self.get_response = Mock(return_value=JsonResponse({"ok": True}))
        self.middleware = ServerPermissionMiddleware(self.get_response)

    def test_bypasses_non_assistant_request(self):
        response = self.middleware(RequestFactory().get("/api/v1/deployments/id"))

        self.assertEqual(200, response.status_code)
        self.get_response.assert_called_once()

    def test_requires_executor_header(self):
        response = self.middleware(
            RequestFactory().get(
                "/api/v1/assistant/2517f944-253a-456d-81b9-39820defa742"
            )
        )

        self.assertEqual(403, response.status_code)
        self.assertEqual(
            {"detail": "Forbidden: missing user email header."},
            loads(response.content),
        )
        self.get_response.assert_not_called()

    def test_requires_chat_permission(self):
        user = SimpleNamespace()
        request = RequestFactory().get(
            "/api/v1/assistant/2517f944-253a-456d-81b9-39820defa742",
            HTTP_X_EXECUTOR="Operator@Example.com",
        )

        with (
            patch.object(
                PermissionService,
                "resolve_user_by_email",
                return_value=user,
            ) as resolve_user,
            patch.object(PermissionService, "can", return_value=False) as can,
        ):
            response = self.middleware(request)

        self.assertEqual(403, response.status_code)
        resolve_user.assert_called_once_with("operator@example.com")
        can.assert_called_once_with(
            user=user,
            model="Conservation",
            action="chat",
        )

    def test_passes_authorized_request(self):
        request = RequestFactory().get(
            "/api/v1/assistant/2517f944-253a-456d-81b9-39820defa742",
            HTTP_X_EXECUTOR="operator@example.com",
        )
        user = SimpleNamespace()
        with (
            patch.object(PermissionService, "resolve_user_by_email", return_value=user),
            patch.object(PermissionService, "can", return_value=True),
        ):
            response = self.middleware(request)

        self.assertEqual(200, response.status_code)
        self.assertIs(user, request.executor_user)
        self.get_response.assert_called_once_with(request)

    @override_settings(
        SERVER_PATH_PREFIX="gateway/v2",
        SERVER_PATH_ASSISTANT="copilot",
    )
    def test_uses_configured_path(self):
        self.assertTrue(
            self.middleware._is_assistant_request(
                RequestFactory().get(
                    "/gateway/v2/copilot/2517f944-253a-456d-81b9-39820defa742"
                )
            )
        )
        self.assertFalse(
            self.middleware._is_assistant_request(
                RequestFactory().get(
                    "/api/v1/assistant/2517f944-253a-456d-81b9-39820defa742"
                )
            )
        )
