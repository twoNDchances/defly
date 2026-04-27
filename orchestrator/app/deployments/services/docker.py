from json import dumps
from os import getenv
from re import sub
from typing import Any

from docker import DockerClient
from docker.errors import BuildError, NotFound

COMPOSE_PROJECT_LABEL = "com.docker.compose.project"
COMPOSE_SERVICE_LABEL = "com.docker.compose.service"
COMPOSE_ONEOFF_LABEL = "com.docker.compose.oneoff"
COMPOSE_VERSION_LABEL = "com.docker.compose.version"
COMPOSE_VOLUME_LABEL = "com.docker.compose.volume"
COMPOSE_PROJECT_CONFIG_FILES_LABEL = "com.docker.compose.project.config_files"
COMPOSE_PROJECT_WORKING_DIR_LABEL = "com.docker.compose.project.working_dir"


class DockerService:
    @staticmethod
    def normalize_environment_variables(
        raw_environment_variables: Any,
    ) -> dict[str, str]:
        if raw_environment_variables is None:
            return {}
        if not isinstance(raw_environment_variables, dict):
            raise ValueError("environment_variables must be a JSON object.")

        normalized_variables: dict[str, str] = {}
        for key, value in raw_environment_variables.items():
            normalized_key = str(key).strip()
            if not normalized_key:
                raise ValueError("environment_variables contains an empty key.")
            normalized_variables[normalized_key] = "" if value is None else str(value)

        return normalized_variables

    @staticmethod
    def get_container_name(defender_name: str) -> str:
        normalized_name = sub(r"[^a-zA-Z0-9_.-]", "-", defender_name.strip()).lower()
        normalized_name = normalized_name.strip(".-_")
        if not normalized_name:
            raise RuntimeError("Invalid defender name for Docker container.")
        return normalized_name

    @staticmethod
    def build_and_run_container(
        *,
        defender_id: str,
        defender_name: str,
        proxy_port: int,
        environment_variables: dict[str, str],
        source_directory: str,
    ) -> dict[str, Any]:
        client = DockerClient(base_url="unix:///var/run/docker.sock")
        try:
            image_tag = "defly-defender"
            image, build_logs = client.images.build(
                path=source_directory,
                dockerfile="Dockerfile",
                tag=image_tag,
                rm=True,
                pull=False,
            )
            build_log_lines = DockerService.extract_build_log_lines(build_logs)

            container_name = DockerService.get_container_name(defender_name)
            DockerService._remove_existing_container(client, container_name)

            compose_context = DockerService._resolve_current_container_compose_context(
                client
            )
            network_names = compose_context["network_names"]
            container_labels = DockerService._build_container_labels(
                defender_id=defender_id,
                defender_name=defender_name,
                compose_labels=compose_context["labels"],
            )
            proxy_port_string = str(proxy_port)
            volume_key = f"{container_name}-resources"
            volume_name = DockerService._get_compose_resource_name(
                resource_key=volume_key,
                compose_labels=compose_context["labels"],
            )
            volume_labels = DockerService._build_volume_labels(
                defender_id=defender_id,
                defender_name=defender_name,
                volume_key=volume_key,
                compose_labels=compose_context["labels"],
            )
            resources_volume = client.volumes.create(
                name=volume_name,
                labels=volume_labels,
            )

            container_environment_variables = dict(environment_variables)
            container_environment_variables["PROXY_PORT"] = proxy_port_string

            run_kwargs = {
                "image": image.id,
                "name": container_name,
                "detach": True,
                "environment": container_environment_variables,
                "labels": container_labels,
                "restart_policy": {"Name": "unless-stopped"},
                "volumes": {
                    resources_volume.name: {"bind": "/app/resources", "mode": "rw"}
                },
                "ports": {f"{proxy_port_string}/tcp": proxy_port},
            }
            if network_names:
                run_kwargs["network"] = network_names[0]

            container = client.containers.run(**run_kwargs)

            for network_name in network_names[1:]:
                client.networks.get(network_name).connect(container)

            container.reload()
            container_networks = list(
                container.attrs.get("NetworkSettings", {}).get("Networks", {}).keys()
            )

            return {
                "image": image.tags[0] if image.tags else image.id,
                "container_id": container.id,
                "container_name": container.name,
                "container_networks": container_networks,
                "resources_volume": resources_volume.name,
                "resources_volume_key": volume_key,
                "proxy_port": proxy_port,
                "compose_project": compose_context["labels"].get(COMPOSE_PROJECT_LABEL),
                "compose_service": container_labels.get(COMPOSE_SERVICE_LABEL),
                "build_logs_tail": build_log_lines,
            }
        finally:
            client.close()

    @staticmethod
    def cancel_container(*, defender_name: str) -> dict[str, Any]:
        client = DockerClient(base_url="unix:///var/run/docker.sock")
        try:
            container_name = DockerService.get_container_name(defender_name)
            try:
                container = client.containers.get(container_name)
            except NotFound:
                return {"container_name": container_name, "removed": False}

            container_id = container.id
            container.remove(force=True)
            return {
                "container_id": container_id,
                "container_name": container_name,
                "removed": True,
            }
        finally:
            client.close()

    @staticmethod
    def cleanup_container(*, defender_name: str) -> None:
        client = DockerClient(base_url="unix:///var/run/docker.sock")
        try:
            container_name = DockerService.get_container_name(defender_name)
            DockerService._remove_existing_container(client, container_name)
        finally:
            client.close()

    @staticmethod
    def get_container_error_logs(*, defender_name: str) -> list[str] | None:
        return DockerService.get_container_logs(defender_name=defender_name)

    @staticmethod
    def get_container_logs(*, defender_name: str) -> list[str] | None:
        client = DockerClient(base_url="unix:///var/run/docker.sock")
        try:
            container_name = DockerService.get_container_name(defender_name)
            try:
                container = client.containers.get(container_name)
            except NotFound:
                return None

            raw_logs = container.logs(stdout=True, stderr=True, tail="all")
            if not raw_logs:
                return None

            decoded_logs = raw_logs.decode("utf-8", errors="replace")
            if decoded_logs == "":
                return None

            return DockerService.split_lines_preserve_newline(decoded_logs)
        finally:
            client.close()

    @staticmethod
    def stringify_deploy_error(exception: Exception) -> list[str]:
        if isinstance(exception, BuildError):
            if exception.build_log:
                log_lines = DockerService.extract_build_log_lines(exception.build_log)
                if log_lines:
                    return log_lines
            if exception.msg:
                return DockerService.split_lines_preserve_newline(str(exception.msg))
        return DockerService.split_lines_preserve_newline(str(exception))

    @staticmethod
    def serialize_deployment_details(payload: dict[str, Any]) -> str:
        return dumps(payload, ensure_ascii=True)

    @staticmethod
    def serialize_log_lines(log_lines: list[str]) -> str:
        return dumps(log_lines, ensure_ascii=True)

    @staticmethod
    def extract_build_log_lines(build_logs: list[dict[str, Any]]) -> list[str]:
        log_lines: list[str] = []
        for line in build_logs:
            if not isinstance(line, dict):
                continue
            for key in ("stream", "error", "message"):
                message = line.get(key)
                if isinstance(message, str):
                    log_lines.extend(
                        DockerService.split_lines_preserve_newline(message)
                    )
            error_detail = line.get("errorDetail")
            if isinstance(error_detail, dict):
                detail_message = error_detail.get("message")
                if isinstance(detail_message, str):
                    log_lines.extend(
                        DockerService.split_lines_preserve_newline(detail_message)
                    )
        return log_lines

    @staticmethod
    def split_lines_preserve_newline(text: str) -> list[str]:
        return text.splitlines()

    @staticmethod
    def _resolve_current_container_networks(client: DockerClient) -> list[str]:
        return DockerService._resolve_current_container_compose_context(client)[
            "network_names"
        ]

    @staticmethod
    def _resolve_current_container_compose_context(
        client: DockerClient,
    ) -> dict[str, Any]:
        container_id = getenv("HOSTNAME", "").strip()
        if not container_id:
            return {"labels": {}, "network_names": []}

        try:
            current_container = client.containers.get(container_id)
        except NotFound:
            return {"labels": {}, "network_names": []}

        return {
            "labels": current_container.attrs.get("Config", {}).get("Labels", {}) or {},
            "network_names": list(
                current_container.attrs.get("NetworkSettings", {})
                .get("Networks", {})
                .keys()
            ),
        }

    @staticmethod
    def _build_container_labels(
        *,
        defender_id: str,
        defender_name: str,
        compose_labels: dict[str, str],
    ) -> dict[str, str]:
        labels = {
            "defly.service": "defender",
            "defly.defender_id": defender_id,
            "defly.defender_name": defender_name,
        }

        compose_project = compose_labels.get(COMPOSE_PROJECT_LABEL)
        compose_service = compose_labels.get(COMPOSE_SERVICE_LABEL)
        if not compose_project or not compose_service:
            return labels

        labels.update(
            {
                COMPOSE_PROJECT_LABEL: compose_project,
                COMPOSE_SERVICE_LABEL: compose_service,
                COMPOSE_ONEOFF_LABEL: "False",
                "defly.compose.attached_service": compose_service,
            }
        )

        for label_name in (
            COMPOSE_VERSION_LABEL,
            COMPOSE_PROJECT_CONFIG_FILES_LABEL,
            COMPOSE_PROJECT_WORKING_DIR_LABEL,
        ):
            label_value = compose_labels.get(label_name)
            if label_value:
                labels[label_name] = label_value

        return labels

    @staticmethod
    def _build_volume_labels(
        *,
        defender_id: str,
        defender_name: str,
        volume_key: str,
        compose_labels: dict[str, str],
    ) -> dict[str, str]:
        labels = {
            "defly.service": "defender",
            "defly.defender_id": defender_id,
            "defly.defender_name": defender_name,
            "defly.volume": "resources",
        }

        compose_project = compose_labels.get(COMPOSE_PROJECT_LABEL)
        if not compose_project:
            return labels

        labels.update(
            {
                COMPOSE_PROJECT_LABEL: compose_project,
                COMPOSE_VOLUME_LABEL: volume_key,
            }
        )

        for label_name in (
            COMPOSE_VERSION_LABEL,
            COMPOSE_PROJECT_CONFIG_FILES_LABEL,
            COMPOSE_PROJECT_WORKING_DIR_LABEL,
        ):
            label_value = compose_labels.get(label_name)
            if label_value:
                labels[label_name] = label_value

        return labels

    @staticmethod
    def _get_compose_resource_name(
        *,
        resource_key: str,
        compose_labels: dict[str, str],
    ) -> str:
        compose_project = compose_labels.get(COMPOSE_PROJECT_LABEL)
        if not compose_project:
            return resource_key

        return f"{compose_project}_{resource_key}"

    @staticmethod
    def _remove_existing_container(client: DockerClient, container_name: str) -> None:
        try:
            existing_container = client.containers.get(container_name)
        except NotFound:
            return

        existing_container.remove(force=True)
