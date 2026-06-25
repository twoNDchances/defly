from django.test import SimpleTestCase

from app.bases.exceptions import DeflyError, DeploymentError, DockerServiceError


class ExceptionHierarchyTests(SimpleTestCase):
    def test_deployment_exceptions_share_application_base(self):
        self.assertTrue(issubclass(DeploymentError, DeflyError))
        self.assertTrue(issubclass(DockerServiceError, DeploymentError))
