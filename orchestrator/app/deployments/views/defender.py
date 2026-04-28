from asyncio import to_thread

from django.conf import settings
from django.http import HttpRequest, HttpResponseNotAllowed, JsonResponse
from django.utils import timezone
from django.views import View
from docker.errors import APIError, BuildError, DockerException

from app.deployments.models import Defenders
from app.deployments.services.docker import DockerService


class DefenderView(View):
    async def get(self, request: HttpRequest, *args, **kwargs):
        return await self._handle_action(request, *args, **kwargs)

    async def post(self, request: HttpRequest, *args, **kwargs):
        return await self._handle_action(request, *args, **kwargs)

    async def put(self, request: HttpRequest, *args, **kwargs):
        return await self._handle_action(request, *args, **kwargs)

    async def patch(self, request: HttpRequest, *args, **kwargs):
        return await self._handle_action(request, *args, **kwargs)

    async def delete(self, request: HttpRequest, *args, **kwargs):
        return await self._handle_action(request, *args, **kwargs)

    async def _handle_action(self, request: HttpRequest, *args, **kwargs):
        method = request.method.lower()
        deploy_method = settings.SERVER_METHOD_DEPLOY.lower()
        follow_method = settings.SERVER_METHOD_FOLLOW.lower()
        cancel_method = settings.SERVER_METHOD_CANCEL.lower()

        if method == deploy_method:
            return await self._deploy_defender(*args, **kwargs)
        if method == follow_method:
            return await self._follow_defender(*args, **kwargs)
        if method == cancel_method:
            return await self._cancel_defender(*args, **kwargs)

        allowed_methods = sorted(
            {deploy_method.upper(), follow_method.upper(), cancel_method.upper()}
        )
        return HttpResponseNotAllowed(allowed_methods)

    async def _deploy_defender(self, *args, **kwargs):
        defender_id = kwargs.get("defender_id")
        defender = await Defenders.objects.filter(id=defender_id).afirst()
        if defender is None:
            return JsonResponse({"detail": "Defender not found."}, status=404)

        try:
            environment_variables = DockerService.normalize_environment_variables(
                defender.environment_variables
            )
        except ValueError as exception:
            error_logs = DockerService.split_lines_preserve_newline(str(exception))
            await self._mark_failed(defender_id=defender_id, error_logs=error_logs)
            return JsonResponse({"detail": str(exception)}, status=400)

        await Defenders.objects.filter(id=defender_id).aupdate(
            deployment_status=Defenders.DeploymentStatus.PROCESSING,
            deployment_details={"message": "Starting deployment..."},
            updated_at=timezone.now(),
        )

        try:
            deployment_result = await to_thread(
                DockerService.build_and_run_container,
                defender_id=str(defender.id),
                defender_name=defender.name,
                proxy_port=defender.proxy_port,
                environment_variables=environment_variables,
                source_directory=settings.SERVER_SOURCE_DEFENDER,
            )
        except (BuildError, APIError, DockerException, RuntimeError) as exception:
            docker_error = DockerService.stringify_deploy_error(exception)
            container_logs = None
            try:
                container_logs = await to_thread(
                    DockerService.get_container_error_logs,
                    defender_name=defender.name,
                )
            except DockerException, RuntimeError:
                container_logs = None
            try:
                await to_thread(
                    DockerService.cleanup_container,
                    defender_name=defender.name,
                )
            except DockerException, RuntimeError:
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

    async def _follow_defender(self, *args, **kwargs):
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
        except (DockerException, RuntimeError) as exception:
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

    async def _cancel_defender(self, *args, **kwargs):
        defender_id = kwargs.get("defender_id")
        defender = await Defenders.objects.filter(id=defender_id).afirst()
        if defender is None:
            return JsonResponse({"detail": "Defender not found."}, status=404)

        try:
            cancellation_result = await to_thread(
                DockerService.cancel_container,
                defender_name=defender.name,
            )
        except (APIError, DockerException, RuntimeError) as exception:
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
