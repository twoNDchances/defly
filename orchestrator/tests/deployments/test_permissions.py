from types import SimpleNamespace
from unittest.mock import AsyncMock, patch

from django.core.exceptions import PermissionDenied
from django.test import SimpleTestCase, override_settings

from app.bases.services.permissions import PermissionService
from app.deployments.services.permissions import DefenderPermissionService


class DefenderPermissionServiceTests(SimpleTestCase):
    @override_settings(
        SERVER_METHOD_DEPLOY="put",
        SERVER_METHOD_FOLLOW="post",
        SERVER_METHOD_CANCEL="patch",
    )
    def test_resolves_action_from_configured_method(self):
        self.assertEqual(
            "deploy",
            DefenderPermissionService.resolve_action_from_method(" PUT "),
        )
        self.assertEqual(
            "follow",
            DefenderPermissionService.resolve_action_from_method("post"),
        )
        self.assertEqual(
            "cancel",
            DefenderPermissionService.resolve_action_from_method("PATCH"),
        )
        self.assertIsNone(
            DefenderPermissionService.resolve_action_from_method("delete")
        )

    async def test_allows_authorized_defender_action(self):
        user = SimpleNamespace()

        with patch.object(
            PermissionService,
            "acan",
            new=AsyncMock(return_value=True),
        ) as can:
            await DefenderPermissionService.validate_action(
                user=user,
                action="deploy",
            )

        can.assert_awaited_once_with(
            user=user,
            model="Defender",
            action="deploy",
        )

    async def test_rejects_unsupported_or_unauthorized_action(self):
        with (
            patch.object(PermissionService, "acan", new=AsyncMock()) as can,
            self.assertRaises(PermissionDenied),
        ):
            await DefenderPermissionService.validate_action(
                user=SimpleNamespace(),
                action="unknown",
            )
        can.assert_not_awaited()

        with (
            patch.object(
                PermissionService,
                "acan",
                new=AsyncMock(return_value=False),
            ) as can,
            self.assertRaises(PermissionDenied),
        ):
            await DefenderPermissionService.validate_action(
                user=None,
                action="cancel",
            )
        can.assert_awaited_once_with(
            user=None,
            model="Defender",
            action="cancel",
        )
