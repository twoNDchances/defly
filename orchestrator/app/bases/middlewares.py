from base64 import b64decode
from binascii import Error as BinasciiError
from functools import lru_cache
from hmac import compare_digest
from socket import gaierror, getaddrinfo

from django.conf import settings
from django.http import JsonResponse
from django.http.request import HttpRequest


class ServerHelperMiddleware:
    @staticmethod
    def get_client_ip(request: HttpRequest) -> str:
        forwarded_for = request.META.get("HTTP_X_FORWARDED_FOR", "")
        if forwarded_for:
            return forwarded_for.split(",")[0].strip()
        return request.META.get("REMOTE_ADDR", "").strip()

    @staticmethod
    @lru_cache(maxsize=32)
    def resolve_host_ips(host: str) -> set[str]:
        if not host:
            return set()

        ips = {host}
        try:
            for _, _, _, _, sockaddr in getaddrinfo(host, None):
                if sockaddr and sockaddr[0]:
                    ips.add(sockaddr[0])
        except gaierror:
            return ips

        return ips

    @staticmethod
    def parse_basic_auth(header_value: str) -> tuple[str | None, str | None]:
        if not header_value:
            return None, None

        scheme, _, payload = header_value.partition(" ")
        if scheme.lower() != "basic" or not payload:
            return None, None

        try:
            decoded = b64decode(payload, validate=True).decode("utf-8")
        except BinasciiError, UnicodeDecodeError:
            return None, None

        username, separator, password = decoded.partition(":")
        if not separator:
            return None, None

        return username, password


class ServerManagerOnlyMiddleware(ServerHelperMiddleware):
    def __init__(self, get_response):
        self.get_response = get_response

    def __call__(self, request):
        manager_host = getattr(settings, "SERVER_MANAGER", "manager").strip()
        client_ip = self.get_client_ip(request)

        if manager_host and client_ip not in self.resolve_host_ips(manager_host):
            return JsonResponse(
                {"detail": "Forbidden: client is not allowed."},
                status=403,
            )

        return self.get_response(request)


class ServerBasicAuthMiddleware(ServerHelperMiddleware):
    def __init__(self, get_response):
        self.get_response = get_response

    def __call__(self, request):
        expected_username = str(
            getattr(settings, "SERVER_USERNAME", "defly-orchestrator")
        )
        expected_password = str(getattr(settings, "SERVER_PASSWORD", "P@55w0rd"))
        username, password = self.parse_basic_auth(
            request.META.get("HTTP_AUTHORIZATION", "")
        )

        is_valid = (
            username is not None
            and password is not None
            and compare_digest(username, expected_username)
            and compare_digest(password, expected_password)
        )

        if not is_valid:
            response = JsonResponse({"detail": "Unauthorized"}, status=401)
            response["WWW-Authenticate"] = 'Basic realm="orchestrator"'
            return response

        return self.get_response(request)
