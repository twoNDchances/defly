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

        with (
            patch.object(
                PermissionService,
                "acan",
                new=AsyncMock(return_value=True),
            ) as can,
            patch.object(
                DefenderPermissionService,
                "user_can_operate_defender",
                new=AsyncMock(return_value=True),
            ) as can_operate,
        ):
            await DefenderPermissionService.validate_action(
                user=user,
                action="deploy",
                defender_id="defender-id",
            )

        can.assert_awaited_once_with(
            user=user,
            model="Defender",
            action="deploy",
        )
        can_operate.assert_awaited_once_with(user=user, defender_id="defender-id")

    async def test_rejects_guarded_defender_when_user_cannot_operate_it(self):
        with (
            patch.object(
                PermissionService,
                "acan",
                new=AsyncMock(return_value=True),
            ),
            patch.object(
                DefenderPermissionService,
                "user_can_operate_defender",
                new=AsyncMock(return_value=False),
            ) as can_operate,
            self.assertRaises(PermissionDenied),
        ):
            await DefenderPermissionService.validate_action(
                user=SimpleNamespace(),
                action="deploy",
                defender_id="guarded-defender-id",
            )

        can_operate.assert_awaited_once()

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

    async def test_allows_defender_owner_before_guard_checks(self):
        user = SimpleNamespace(id="user-id")
        owner_query = SimpleNamespace(aexists=AsyncMock(return_value=True))

        with (
            patch(
                "app.deployments.services.permissions.Defenders.objects.filter",
                return_value=owner_query,
            ) as defender_filter,
            patch(
                "app.deployments.services.permissions.Guard.objects.filter",
            ) as guard_filter,
        ):
            allowed = await DefenderPermissionService.user_can_operate_defender(
                user=user,
                defender_id="defender-id",
            )

        self.assertTrue(allowed)
        defender_filter.assert_called_once_with(id="defender-id", created_by="user-id")
        owner_query.aexists.assert_awaited_once()
        guard_filter.assert_not_called()
