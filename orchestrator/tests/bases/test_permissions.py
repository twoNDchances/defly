from types import SimpleNamespace
from unittest.mock import AsyncMock, Mock, patch

from django.test import SimpleTestCase

from app.bases.models import User
from app.bases.services.permissions import PermissionService


def relation(*, exists: bool) -> Mock:
    manager = Mock()
    manager.filter.return_value.exists.return_value = exists
    return manager


def async_relation(*, exists: bool) -> Mock:
    manager = Mock()
    manager.filter.return_value.aexists = AsyncMock(return_value=exists)
    return manager


class PermissionServiceTests(SimpleTestCase):
    def test_resolves_user_by_normalized_email(self):
        self.assertIsNone(PermissionService.resolve_user_by_email("  "))
        user = SimpleNamespace()
        query = Mock()
        query.first.return_value = user
        with patch.object(User.objects, "filter", return_value=query) as filter_user:
            result = PermissionService.resolve_user_by_email(" User@Example.com ")

        self.assertIs(user, result)
        filter_user.assert_called_once_with(email__iexact="user@example.com")

    def test_checks_direct_and_group_permissions(self):
        direct_user = SimpleNamespace(
            permissions=relation(exists=True),
            groups=relation(exists=False),
        )
        self.assertTrue(
            PermissionService.check_permission(
                user=direct_user,
                model="app.Defender",
                action="deploy",
            )
        )
        direct_user.groups.filter.assert_not_called()

        group_user = SimpleNamespace(
            permissions=relation(exists=False),
            groups=relation(exists=True),
        )
        self.assertTrue(
            PermissionService.check_permission(
                user=group_user,
                model="Defender",
                action="follow",
            )
        )

    async def test_checks_direct_and_group_permissions_asynchronously(self):
        direct_user = SimpleNamespace(
            permissions=async_relation(exists=True),
            groups=async_relation(exists=False),
        )
        self.assertTrue(
            await PermissionService.acheck_permission(
                user=direct_user,
                model="app.Defender",
                action="deploy",
            )
        )
        direct_user.groups.filter.assert_not_called()

        group_user = SimpleNamespace(
            permissions=async_relation(exists=False),
            groups=async_relation(exists=True),
        )
        self.assertTrue(
            await PermissionService.acheck_permission(
                user=group_user,
                model="Defender",
                action="follow",
            )
        )

    def test_can_handles_absent_inactive_root_all_and_action_permissions(self):
        self.assertFalse(
            PermissionService.can(user=None, model="Defender", action="deploy")
        )
        inactive = SimpleNamespace(
            is_verified=False,
            is_activated=True,
            is_root=False,
        )
        self.assertFalse(
            PermissionService.can(
                user=inactive,
                model="Defender",
                action="deploy",
            )
        )

        root = SimpleNamespace(is_verified=True, is_activated=True, is_root=True)
        self.assertTrue(
            PermissionService.can(user=root, model="Defender", action="deploy")
        )

        user = SimpleNamespace(is_verified=True, is_activated=True, is_root=False)
        with patch.object(
            PermissionService,
            "check_permission",
            side_effect=(True,),
        ) as check:
            self.assertTrue(
                PermissionService.can(user=user, model="Defender", action="deploy")
            )
        check.assert_called_once_with(user=user, model="Defender", action="all")

        with patch.object(
            PermissionService,
            "check_permission",
            side_effect=(False, True),
        ) as check:
            self.assertTrue(
                PermissionService.can(user=user, model="Defender", action="deploy")
            )
        self.assertEqual(2, check.call_count)

    async def test_async_can_handles_absent_inactive_root_all_and_action_permissions(
        self,
    ):
        self.assertFalse(
            await PermissionService.acan(
                user=None,
                model="Defender",
                action="deploy",
            )
        )
        inactive = SimpleNamespace(
            is_verified=False,
            is_activated=True,
            is_root=False,
        )
        self.assertFalse(
            await PermissionService.acan(
                user=inactive,
                model="Defender",
                action="deploy",
            )
        )

        root = SimpleNamespace(is_verified=True, is_activated=True, is_root=True)
        self.assertTrue(
            await PermissionService.acan(
                user=root,
                model="Defender",
                action="deploy",
            )
        )

        user = SimpleNamespace(is_verified=True, is_activated=True, is_root=False)
        with patch.object(
            PermissionService,
            "acheck_permission",
            new=AsyncMock(side_effect=(True,)),
        ) as check:
            self.assertTrue(
                await PermissionService.acan(
                    user=user,
                    model="Defender",
                    action="deploy",
                )
            )
        check.assert_awaited_once_with(user=user, model="Defender", action="all")

        with patch.object(
            PermissionService,
            "acheck_permission",
            new=AsyncMock(side_effect=(False, True)),
        ) as check:
            self.assertTrue(
                await PermissionService.acan(
                    user=user,
                    model="Defender",
                    action="deploy",
                )
            )
        self.assertEqual(2, check.await_count)

    def test_active_and_verified_requires_both_flags(self):
        self.assertTrue(
            PermissionService.user_is_active_and_verified(
                SimpleNamespace(is_verified=True, is_activated=True)
            )
        )
        self.assertFalse(
            PermissionService.user_is_active_and_verified(
                SimpleNamespace(is_verified=True, is_activated=False)
            )
        )
