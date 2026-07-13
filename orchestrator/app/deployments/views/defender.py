from asyncio import to_thread

from django.core.exceptions import PermissionDenied
from django.http import HttpRequest, JsonResponse
from django.utils import timezone
from docker.errors import APIError, BuildError, DockerException

from app.bases.exceptions import DockerServiceError
from app.bases.views import ConfiguredMethodView
from app.deployments.models import Defenders
from app.deployments.services.docker import DockerService
from app.deployments.services.permissions import DefenderPermissionService


class DefenderView(ConfiguredMethodView):
    method_handler_names = {
        "SERVER_METHOD_DEPLOY": "_deploy_defender",
        "SERVER_METHOD_FOLLOW": "_follow_defender",
        "SERVER_METHOD_CANCEL": "_cancel_defender",
    }

    async def dispatch(self, request: HttpRequest, *args, **kwargs):
        action = getattr(request, "defender_action", None)
        if action is None:
            action = DefenderPermissionService.resolve_action_from_method(
                request.method
            )

        if action is not None:
            try:
                await DefenderPermissionService.validate_action(
                    user=getattr(request, "executor_user", None),
                    action=action,
                    defender_id=kwargs.get("defender_id"),
                )
            except PermissionDenied:
                return JsonResponse(
                    {"detail": "Forbidden: user does not have permission."},
                    status=403,
                )

        return await super().dispatch(request, *args, **kwargs)

    async def _deploy_defender(self, request: HttpRequest, *args, **kwargs):
        defender_id = kwargs.get("defender_id")
        defender = await Defenders.objects.filter(id=defender_id).afirst()
        if defender is None:
            return JsonResponse({"detail": "Defender not found."}, status=404)

        try:
            environment_variables = DockerService.normalize_environment_variables(
                defender.environment_variables
            )
        except DockerServiceError as exception:
            error_logs = DockerService.split_lines_preserve_newline(str(exception))
            await self._mark_failed(defender_id=defender_id, error_logs=error_logs)
            return JsonResponse({"detail": str(exception)}, status=400)

        await self._mark_processing(
            defender_id=defender_id,
            message="Starting deployment...",
        )

        try:
            deployment_result = await to_thread(
                DockerService.build_and_run_container,
                defender_id=str(defender.id),
                defender_name=defender.name,
                proxy_port=defender.proxy_port,
                environment_variables=environment_variables,
            )
        except (BuildError, APIError, DockerException, DockerServiceError) as exception:
            docker_error = DockerService.stringify_deploy_error(exception)
            container_logs = None
            try:
                container_logs = await to_thread(
                    DockerService.get_container_error_logs,
                    defender_name=defender.name,
                )
            except DockerException, DockerServiceError:
                container_logs = None
            try:
                await to_thread(
                    DockerService.cleanup_container,
                    defender_name=defender.name,
                )
            except DockerException, DockerServiceError:
                pass
            error_logs = container_logs if container_logs is not None else docker_error
            await self._mark_failed(defender_id=defender_id, error_logs=error_logs)
            return JsonResponse(
                {"detail": "Defender deployment failed.", "error": error_logs},
                status=500,
            )

        await Defenders.objects.filter(id=defender_id).aupdate(
            deployment_status=Defenders.DeploymentStatus.SUCCESSFUL,
            deployment_details=deployment_result,
            updated_at=timezone.now(),
        )

        return JsonResponse(
            {
                "detail": "Defender deployed successfully.",
                "deployment": deployment_result,
            }
        )

    async def _follow_defender(self, request: HttpRequest, *args, **kwargs):
        defender_id = kwargs.get("defender_id")
        defender = await Defenders.objects.filter(id=defender_id).afirst()
        if defender is None:
            return JsonResponse({"detail": "Defender not found."}, status=404)

        if defender.deployment_status != Defenders.DeploymentStatus.SUCCESSFUL:
            return JsonResponse(
                {"detail": "Defender is not in successful deployment status."},
                status=409,
            )

        try:
            container_logs = await to_thread(
                DockerService.get_container_logs,
                defender_name=defender.name,
            )
        except (DockerException, DockerServiceError) as exception:
            error_logs = DockerService.stringify_deploy_error(exception)
            return JsonResponse(
                {"detail": "Failed to follow defender logs.", "error": error_logs},
                status=500,
            )

        if not container_logs:
            return JsonResponse(
                {"detail": "Defender container logs are empty or container not found."},
                status=404,
            )

        return JsonResponse(
            {
                "detail": "Defender container logs.",
                "container_name": DockerService.get_container_name(defender.name),
                "logs": container_logs[-100:],
            }
        )

    async def _cancel_defender(self, request: HttpRequest, *args, **kwargs):
        defender_id = kwargs.get("defender_id")
        defender = await Defenders.objects.filter(id=defender_id).afirst()
        if defender is None:
            return JsonResponse({"detail": "Defender not found."}, status=404)

        await self._mark_processing(
            defender_id=defender_id,
            message="Starting cancellation...",
        )

        try:
            cancellation_result = await to_thread(
                DockerService.cancel_container,
                defender_name=defender.name,
            )
        except (APIError, DockerException, DockerServiceError) as exception:
            error_logs = DockerService.stringify_deploy_error(exception)
            await self._mark_failed(defender_id=defender_id, error_logs=error_logs)
            return JsonResponse(
                {"detail": "Defender cancel failed.", "error": error_logs},
                status=500,
            )

        if not cancellation_result.get("removed"):
            return JsonResponse(
                {
                    "detail": "Defender container not found.",
                    "cancellation": cancellation_result,
                },
                status=404,
            )

        await Defenders.objects.filter(id=defender_id).aupdate(
            deployment_status=None,
            deployment_details=cancellation_result,
            updated_at=timezone.now(),
        )

        return JsonResponse(
            {
                "detail": "Defender container removed.",
                "cancellation": cancellation_result,
            }
        )

    async def _mark_failed(
        self,
        *,
        defender_id,
        error_logs: list[str],
    ) -> None:
        await Defenders.objects.filter(id=defender_id).aupdate(
            deployment_status=Defenders.DeploymentStatus.FAILED,
            deployment_details=error_logs,
            updated_at=timezone.now(),
        )

    async def _mark_processing(
        self,
        *,
        defender_id,
        message: str,
    ) -> None:
        await Defenders.objects.filter(id=defender_id).aupdate(
            deployment_status=Defenders.DeploymentStatus.PROCESSING,
            deployment_details={"message": message},
            updated_at=timezone.now(),
        )
