from types import SimpleNamespace
from unittest.mock import AsyncMock, call, patch

from django.core.exceptions import PermissionDenied
from django.test import SimpleTestCase

from app.assistant.services.permissions import ResourcePermissionService
from app.bases.services.permissions import PermissionService


class ResourcePermissionServiceTests(SimpleTestCase):
    async def test_messages_without_attachments_do_not_require_resource_permissions(
        self,
    ):
        messages = [
            {"role": "user", "content": "No attachments"},
            {"role": "assistant", "content": "Empty attachments", "resources": []},
        ]

        with patch.object(PermissionService, "acan", new=AsyncMock()) as can:
            await ResourcePermissionService.validate_messages(
                messages=messages,
                user=None,
            )

        can.assert_not_awaited()

    async def test_requires_list_and_view_permissions_for_every_resource(self):
        user = SimpleNamespace()
        messages = [
            {
                "role": "user",
                "content": "Review these",
                "resources": [
                    {"type": " Label ", "id": "label-id"},
                    {"type": "defender", "id": "defender-id"},
                ],
            }
        ]

        with patch.object(
            PermissionService,
            "acan",
            new=AsyncMock(return_value=True),
        ) as can:
            await ResourcePermissionService.validate_messages(
                messages=messages,
                user=user,
            )

        self.assertEqual(
            [
                call(user=user, model="Label", action="viewAny"),
                call(user=user, model="Label", action="view"),
                call(user=user, model="Defender", action="viewAny"),
                call(user=user, model="Defender", action="view"),
            ],
            can.await_args_list,
        )

    async def test_rejects_resources_without_list_or_view_permission(self):
        resource = {"type": "label", "id": "label-id"}
        user = SimpleNamespace()

        for permissions, expected_calls in (
            ([False], 1),
            ([True, False], 2),
        ):
            with self.subTest(permissions=permissions):
                with (
                    patch.object(
                        PermissionService,
                        "acan",
                        new=AsyncMock(side_effect=permissions),
                    ) as can,
                    self.assertRaises(PermissionDenied),
                ):
                    await ResourcePermissionService.validate_resource(
                        resource=resource,
                        user=user,
                    )

                self.assertEqual(expected_calls, can.await_count)

    async def test_rejects_malformed_or_unsupported_resources(self):
        for messages in (
            [{"role": "user", "content": "Invalid", "resources": "invalid"}],
            [{"role": "user", "content": "Invalid", "resources": ["invalid"]}],
            [{"role": "user", "content": "Invalid", "resources": [{}]}],
            [
                {
                    "role": "user",
                    "content": "Invalid",
                    "resources": [{"type": "unknown"}],
                }
            ],
        ):
            with self.subTest(messages=messages), self.assertRaises(PermissionDenied):
                await ResourcePermissionService.validate_messages(
                    messages=messages,
                    user=SimpleNamespace(),
                )
