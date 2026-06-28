class DeflyError(Exception):
    """Base exception for errors raised by Defly application services."""


class AssistantError(DeflyError):
    """Base exception for assistant-related errors."""


class AssistantServiceError(AssistantError):
    """Raised when the assistant service cannot complete a request."""


class DeploymentError(DeflyError):
    """Base exception for deployment-related errors."""


class DockerServiceError(DeploymentError):
    """Raised when the Docker service cannot complete an operation."""


__all__ = [
    "AssistantError",
    "AssistantServiceError",
    "DeflyError",
    "DeploymentError",
    "DockerServiceError",
]
