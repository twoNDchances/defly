from unittest.mock import MagicMock, Mock, patch

from django.test import SimpleTestCase, override_settings
from docker.errors import ImageNotFound, NotFound

from app.bases.exceptions import DockerServiceError
from app.deployments.services.docker import DockerService


def docker_client() -> MagicMock:
    client = MagicMock()
    client.close = Mock()
    return client


class DockerServiceValueTests(SimpleTestCase):
    def test_creates_client_from_settings(self):
        with (
            override_settings(SERVER_DOCKER_BASE_URL="tcp://docker:2375"),
            patch("app.deployments.services.docker.DockerClient") as client_class,
        ):
            result = DockerService.get_client()

        self.assertIs(result, client_class.return_value)
        client_class.assert_called_once_with(base_url="tcp://docker:2375")

    def test_normalizes_environment_variables(self):
        self.assertEqual({}, DockerService.normalize_environment_variables(None))
        self.assertEqual(
            {"KEY": "1", "NONE": ""},
            DockerService.normalize_environment_variables({" KEY ": 1, "NONE": None}),
        )

        for value, message in (
            ([], "must be a JSON object"),
            ({" ": "value"}, "contains an empty key"),
        ):
            with (
                self.subTest(value=value),
                self.assertRaisesMessage(DockerServiceError, message),
            ):
                DockerService.normalize_environment_variables(value)

    def test_normalizes_container_name(self):
        self.assertEqual(
            "edge-defender.v1",
            DockerService.get_container_name(" Edge Defender.v1 "),
        )
        with self.assertRaisesMessage(DockerServiceError, "Invalid defender name"):
            DockerService.get_container_name(" --- ")

    @override_settings(SERVER_DEFENDER_IMAGE="")
    def test_requires_defender_image(self):
        client = docker_client()
        with (
            patch.object(DockerService, "get_client", return_value=client),
            self.assertRaisesMessage(DockerServiceError, "cannot be empty"),
        ):
            DockerService.build_and_run_container(
                defender_id="id",
                defender_name="Edge",
                proxy_port=9948,
                environment_variables={},
            )
        client.close.assert_called_once()

    @override_settings(SERVER_DEFENDER_IMAGE="missing:latest")
    def test_reports_missing_defender_image(self):
        client = docker_client()
        client.images.get.side_effect = ImageNotFound("missing")
        with (
            patch.object(DockerService, "get_client", return_value=client),
            self.assertRaisesMessage(DockerServiceError, "does not exist"),
        ):
            DockerService.build_and_run_container(
                defender_id="id",
                defender_name="Edge",
                proxy_port=9948,
                environment_variables={},
            )
        client.close.assert_called_once()

    def test_requires_existing_volume(self):
        client = docker_client()
        DockerService._require_existing_volume(client, "existing")

        missing_client = docker_client()
        missing_client.volumes.get.side_effect = NotFound("missing")
        with self.assertRaisesMessage(DockerServiceError, "does not exist"):
            DockerService._require_existing_volume(missing_client, "missing")
