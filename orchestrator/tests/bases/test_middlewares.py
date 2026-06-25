from base64 import b64encode
from socket import gaierror
from unittest.mock import Mock, patch

from django.http import JsonResponse
from django.test import RequestFactory, SimpleTestCase, override_settings

from app.bases.middlewares import (
    ServerBasicAuthMiddleware,
    ServerHelperMiddleware,
    ServerManagerOnlyMiddleware,
)


class ServerHelperMiddlewareTests(SimpleTestCase):
    def tearDown(self):
        ServerHelperMiddleware.resolve_host_ips.cache_clear()
        super().tearDown()

    def test_normalizes_header_and_client_ip(self):
        self.assertEqual(
            "HTTP_X_EXECUTOR",
            ServerHelperMiddleware.resolve_header_meta_key(" x-executor "),
        )
        forwarded = RequestFactory().get(
            "/",
            HTTP_X_FORWARDED_FOR="203.0.113.1, 10.0.0.1",
        )
        direct = RequestFactory().get("/", REMOTE_ADDR="127.0.0.1")

        self.assertEqual("203.0.113.1", ServerHelperMiddleware.get_client_ip(forwarded))
        self.assertEqual("127.0.0.1", ServerHelperMiddleware.get_client_ip(direct))

    def test_resolves_host_ips_and_handles_dns_failure(self):
        self.assertEqual(set(), ServerHelperMiddleware.resolve_host_ips(""))
        addresses = [
            (None, None, None, None, ("127.0.0.1", 0)),
            (None, None, None, None, None),
        ]
        with patch("app.bases.middlewares.getaddrinfo", return_value=addresses):
            self.assertEqual(
                {"manager", "127.0.0.1"},
                ServerHelperMiddleware.resolve_host_ips("manager"),
            )

        ServerHelperMiddleware.resolve_host_ips.cache_clear()
        with patch("app.bases.middlewares.getaddrinfo", side_effect=gaierror):
            self.assertEqual(
                {"unknown"},
                ServerHelperMiddleware.resolve_host_ips("unknown"),
            )

    def test_parses_basic_auth_variants(self):
        valid = b64encode(b"user:pass:word").decode()
        invalid_utf8 = b64encode(b"\xff").decode()
        no_separator = b64encode(b"user").decode()

        for value in (
            "",
            "Bearer token",
            "Basic ",
            "Basic not-base64",
            f"Basic {invalid_utf8}",
            f"Basic {no_separator}",
        ):
            with self.subTest(value=value):
                self.assertEqual(
                    (None, None),
                    ServerHelperMiddleware.parse_basic_auth(value),
                )

        self.assertEqual(
            ("user", "pass:word"),
            ServerHelperMiddleware.parse_basic_auth(f"Basic {valid}"),
        )


class ServerManagerOnlyMiddlewareTests(SimpleTestCase):
    @override_settings(SERVER_MANAGER=" manager, 127.0.0.1, ")
    def test_lists_allowed_hosts(self):
        self.assertEqual(
            ["manager", "127.0.0.1"],
            ServerManagerOnlyMiddleware.allowed_manager_hosts(),
        )

    @override_settings(SERVER_MANAGER="manager")
    def test_allows_configured_client_and_rejects_other_client(self):
        get_response = Mock(return_value=JsonResponse({"ok": True}))
        middleware = ServerManagerOnlyMiddleware(get_response)

        with patch.object(middleware, "resolve_host_ips", return_value={"10.0.0.1"}):
            allowed = middleware(RequestFactory().get("/", REMOTE_ADDR="10.0.0.1"))
            denied = middleware(RequestFactory().get("/", REMOTE_ADDR="10.0.0.2"))

        self.assertEqual(200, allowed.status_code)
        self.assertEqual(403, denied.status_code)
        get_response.assert_called_once()

    @override_settings(SERVER_MANAGER="")
    def test_empty_allow_list_does_not_restrict_client(self):
        get_response = Mock(return_value=JsonResponse({"ok": True}))
        response = ServerManagerOnlyMiddleware(get_response)(
            RequestFactory().get("/", REMOTE_ADDR="10.0.0.2")
        )
        self.assertEqual(200, response.status_code)


class ServerBasicAuthMiddlewareTests(SimpleTestCase):
    @override_settings(SERVER_USERNAME="user", SERVER_PASSWORD="secret")
    def test_accepts_valid_credentials(self):
        get_response = Mock(return_value=JsonResponse({"ok": True}))
        credentials = b64encode(b"user:secret").decode()
        request = RequestFactory().get(
            "/",
            HTTP_AUTHORIZATION=f"Basic {credentials}",
        )

        response = ServerBasicAuthMiddleware(get_response)(request)

        self.assertEqual(200, response.status_code)
        get_response.assert_called_once_with(request)

    @override_settings(SERVER_USERNAME="user", SERVER_PASSWORD="secret")
    def test_rejects_invalid_credentials(self):
        response = ServerBasicAuthMiddleware(Mock())(RequestFactory().get("/"))

        self.assertEqual(401, response.status_code)
        self.assertEqual('Basic realm="orchestrator"', response["WWW-Authenticate"])
