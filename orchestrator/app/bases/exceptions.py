class DeflyError(Exception):
    """Base exception for errors raised by Defly application services."""


class DeploymentError(DeflyError):
    """Base exception for deployment-related errors."""


class DockerServiceError(DeploymentError):
    """Raised when the Docker service cannot complete an operation."""


__all__ = [
    "DeflyError",
    "DeploymentError",
    "DockerServiceError",
]
