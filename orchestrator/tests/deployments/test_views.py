from json import loads
from unittest.mock import AsyncMock, patch
from uuid import uuid4

from django.core.exceptions import PermissionDenied
from django.http import JsonResponse
from django.test import RequestFactory, SimpleTestCase, override_settings

from app.bases.exceptions import DockerServiceError
from app.deployments.models import Defenders
from app.deployments.services.docker import DockerService
from app.deployments.services.permissions import DefenderPermissionService
from app.deployments.views.defender import DefenderView
from tests.deployments.helpers import make_defender, make_query


class DefenderViewDispatchTests(SimpleTestCase):
    @override_settings(
        SERVER_METHOD_DEPLOY="put",
        SERVER_METHOD_FOLLOW="post",
        SERVER_METHOD_CANCEL="patch",
    )
    async def test_dispatches_configured_action_methods(self):
        defender_id = uuid4()
        cases = [
            ("put", "_deploy_defender", "deploy"),
            ("post", "_follow_defender", "follow"),
            ("patch", "_cancel_defender", "cancel"),
        ]

        for method, handler_name, action in cases:
            with self.subTest(method=method):
                request = RequestFactory().generic(
                    method.upper(),
                    f"/api/v1/deployments/{defender_id}",
                )
                user = object()
                request.executor_user = user
                request.defender_action = action
                handler = AsyncMock(return_value=JsonResponse({"action": action}))
                with (
                    patch.object(DefenderView, handler_name, new=handler),
                    patch.object(
                        DefenderPermissionService,
                        "validate_action",
                    ) as validate_action,
                ):
                    response = await DefenderView.as_view()(
                        request,
                        defender_id=defender_id,
                    )

                self.assertEqual({"action": action}, loads(response.content))
                validate_action.assert_awaited_once_with(
                    user=user,
                    action=action,
                    defender_id=defender_id,
                )
                handler.assert_awaited_once_with(request, defender_id=defender_id)

    @override_settings(SERVER_METHOD_DEPLOY="put")
    async def test_rejects_unauthorized_action_before_handler(self):
        defender_id = uuid4()
        request = RequestFactory().put(f"/api/v1/deployments/{defender_id}")
        request.executor_user = object()
        handler = AsyncMock(return_value=JsonResponse({"ok": True}))

        with (
            patch.object(DefenderView, "_deploy_defender", new=handler),
            patch.object(
                DefenderPermissionService,
                "validate_action",
                side_effect=PermissionDenied,
            ),
        ):
            response = await DefenderView.as_view()(
                request,
                defender_id=defender_id,
            )

        self.assertEqual(403, response.status_code)
        self.assertEqual(
            {"detail": "Forbidden: user does not have permission."},
            loads(response.content),
        )
        handler.assert_not_awaited()

    @override_settings(
        SERVER_METHOD_DEPLOY="put",
        SERVER_METHOD_FOLLOW="post",
        SERVER_METHOD_CANCEL="patch",
    )
    async def test_rejects_unconfigured_method(self):
        response = await DefenderView.as_view()(
            RequestFactory().delete("/api/v1/deployments/id"),
            defender_id=uuid4(),
        )

        self.assertEqual(405, response.status_code)
        self.assertEqual("PATCH, POST, PUT", response.headers["Allow"])


class DefenderDeployViewTests(SimpleTestCase):
    async def test_returns_not_found(self):
        query = make_query()
        with patch.object(Defenders.objects, "filter", return_value=query):
            response = await DefenderView()._deploy_defender(
                RequestFactory().post("/"),
                defender_id=uuid4(),
            )
        self.assertEqual(404, response.status_code)

    async def test_rejects_invalid_environment_and_marks_failed(self):
        defender = make_defender(environment_variables=[])
        query = make_query(defender)
        with patch.object(Defenders.objects, "filter", return_value=query):
            response = await DefenderView()._deploy_defender(
                RequestFactory().post("/"),
                defender_id=defender.id,
            )

        self.assertEqual(400, response.status_code)
        self.assertEqual(
            Defenders.DeploymentStatus.FAILED,
            query.aupdate.call_args.kwargs["deployment_status"],
        )

    async def test_deploys_and_marks_successful(self):
        defender = make_defender()
        query = make_query(defender)
        deployment = {"container_id": "container-id"}
        with (
            patch.object(Defenders.objects, "filter", return_value=query),
            patch(
                "app.deployments.views.defender.to_thread",
                new=AsyncMock(return_value=deployment),
            ) as to_thread,
        ):
            response = await DefenderView()._deploy_defender(
                RequestFactory().post("/"),
                defender_id=defender.id,
            )

        self.assertEqual(200, response.status_code)
        self.assertEqual(deployment, loads(response.content)["deployment"])
        self.assertEqual(2, query.aupdate.await_count)
        to_thread.assert_awaited_once_with(
            DockerService.build_and_run_container,
            defender_id=str(defender.id),
            defender_name=defender.name,
            proxy_port=defender.proxy_port,
            environment_variables={"MODE": "test"},
        )

    async def test_uses_container_logs_when_deployment_fails(self):
        defender = make_defender()
        query = make_query(defender)
        to_thread = AsyncMock(
            side_effect=[
                DockerServiceError("deploy failed"),
                ["container failed"],
                None,
            ]
        )
        with (
            patch.object(Defenders.objects, "filter", return_value=query),
            patch("app.deployments.views.defender.to_thread", new=to_thread),
        ):
            response = await DefenderView()._deploy_defender(
                RequestFactory().post("/"),
                defender_id=defender.id,
            )

        self.assertEqual(500, response.status_code)
        self.assertEqual(["container failed"], loads(response.content)["error"])

    async def test_falls_back_when_error_logs_and_cleanup_fail(self):
        defender = make_defender()
        query = make_query(defender)
        to_thread = AsyncMock(
            side_effect=[
                DockerServiceError("deploy failed"),
                DockerServiceError("logs failed"),
                DockerServiceError("cleanup failed"),
            ]
        )
        with (
            patch.object(Defenders.objects, "filter", return_value=query),
            patch("app.deployments.views.defender.to_thread", new=to_thread),
        ):
            response = await DefenderView()._deploy_defender(
                RequestFactory().post("/"),
                defender_id=defender.id,
            )

        self.assertEqual(["deploy failed"], loads(response.content)["error"])


class DefenderFollowViewTests(SimpleTestCase):
    async def _invoke(self, defender, *, logs=None, error=None):
        query = make_query(defender)
        side_effect = error if error is not None else None
        with (
            patch.object(Defenders.objects, "filter", return_value=query),
            patch(
                "app.deployments.views.defender.to_thread",
                new=AsyncMock(return_value=logs, side_effect=side_effect),
            ),
        ):
            return await DefenderView()._follow_defender(
                RequestFactory().get("/"),
                defender_id=uuid4(),
            )

    async def test_handles_missing_and_non_successful_defender(self):
        self.assertEqual(404, (await self._invoke(None)).status_code)
        pending = make_defender(deployment_status=Defenders.DeploymentStatus.PENDING)
        self.assertEqual(409, (await self._invoke(pending)).status_code)

    async def test_handles_log_errors_and_empty_logs(self):
        defender = make_defender()
        failed = await self._invoke(
            defender,
            error=DockerServiceError("logs failed"),
        )
        empty = await self._invoke(defender, logs=None)
        self.assertEqual(500, failed.status_code)
        self.assertEqual(404, empty.status_code)

    async def test_returns_latest_one_hundred_logs(self):
        response = await self._invoke(
            make_defender(name="Edge Defender"),
            logs=[str(index) for index in range(120)],
        )
        payload = loads(response.content)
        self.assertEqual(200, response.status_code)
        self.assertEqual("edge-defender", payload["container_name"])
        self.assertEqual(100, len(payload["logs"]))


class DefenderCancelViewTests(SimpleTestCase):
    async def _invoke(self, defender, *, result=None, error=None):
        query = make_query(defender)
        with (
            patch.object(Defenders.objects, "filter", return_value=query),
            patch(
                "app.deployments.views.defender.to_thread",
                new=AsyncMock(return_value=result, side_effect=error),
            ),
        ):
            response = await DefenderView()._cancel_defender(
                RequestFactory().delete("/"),
                defender_id=uuid4(),
            )
        return response, query

    async def test_handles_missing_defender_and_cancel_error(self):
        missing, _ = await self._invoke(None)
        failed, query = await self._invoke(
            make_defender(),
            error=DockerServiceError("cancel failed"),
        )
        self.assertEqual(404, missing.status_code)
        self.assertEqual(500, failed.status_code)
        self.assertEqual(2, query.aupdate.await_count)

    async def test_handles_missing_container(self):
        response, _ = await self._invoke(
            make_defender(),
            result={"removed": False},
        )
        self.assertEqual(404, response.status_code)

    async def test_cancels_and_clears_deployment_status(self):
        cancellation = {"removed": True, "container_id": "id"}
        response, query = await self._invoke(
            make_defender(),
            result=cancellation,
        )
        self.assertEqual(200, response.status_code)
        self.assertIsNone(query.aupdate.call_args.kwargs["deployment_status"])
