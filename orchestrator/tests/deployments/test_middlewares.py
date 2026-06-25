from json import loads
from types import SimpleNamespace
from unittest.mock import Mock, patch

from django.http import JsonResponse
from django.test import RequestFactory, SimpleTestCase, override_settings

from app.bases.services.permissions import PermissionService
from app.deployments.middlewares import ServerPermissionMiddleware


class DeploymentPermissionMiddlewareTests(SimpleTestCase):
    def setUp(self):
        self.get_response = Mock(return_value=JsonResponse({"ok": True}))
        self.middleware = ServerPermissionMiddleware(self.get_response)

    def test_bypasses_other_paths_and_unmapped_methods(self):
        other = self.middleware(RequestFactory().get("/api/v1/health"))
        unmapped = self.middleware(
            RequestFactory().patch("/api/v1/deployments/defender-id")
        )

        self.assertEqual(200, other.status_code)
        self.assertEqual(200, unmapped.status_code)
        self.assertEqual(2, self.get_response.call_count)

    def test_requires_executor_header(self):
        response = self.middleware(
            RequestFactory().post("/api/v1/deployments/defender-id")
        )

        self.assertEqual(403, response.status_code)
        self.assertEqual(
            {"detail": "Forbidden: missing user email header."},
            loads(response.content),
        )

    def test_resolves_executor_for_configured_action(self):
        user = SimpleNamespace()
        request = RequestFactory().post(
            "/api/v1/deployments/defender-id",
            HTTP_X_EXECUTOR="Operator@Example.com",
        )
        with patch.object(
            PermissionService,
            "resolve_user_by_email",
            return_value=user,
        ) as resolve_user:
            response = self.middleware(request)

        self.assertEqual(200, response.status_code)
        resolve_user.assert_called_once_with("operator@example.com")
        self.assertIs(user, request.executor_user)
        self.assertEqual("deploy", request.defender_action)

    def test_passes_resolved_executor_to_cancel_request(self):
        request = RequestFactory().delete(
            "/api/v1/deployments/defender-id",
            HTTP_X_EXECUTOR="operator@example.com",
        )
        user = SimpleNamespace()
        with patch.object(
            PermissionService,
            "resolve_user_by_email",
            return_value=user,
        ):
            response = self.middleware(request)

        self.assertEqual(200, response.status_code)
        self.get_response.assert_called_once_with(request)
        self.assertIs(user, request.executor_user)
        self.assertEqual("cancel", request.defender_action)

    @override_settings(
        SERVER_PATH_PREFIX="gateway/v2",
        SERVER_PATH_DEPLOYMENT="agents",
    )
    def test_uses_configured_path(self):
        self.assertTrue(
            self.middleware._is_deployment_request(
                RequestFactory().get("/gateway/v2/agents/id")
            )
        )
        self.assertFalse(
            self.middleware._is_deployment_request(
                RequestFactory().get("/api/v1/deployments/id")
            )
        )
