from types import SimpleNamespace
from unittest.mock import AsyncMock, Mock
from uuid import uuid4

from app.deployments.models import Defenders


def make_defender(**overrides):
    values = {
        "id": uuid4(),
        "name": "Edge Defender",
        "proxy_port": 9948,
        "environment_variables": {"MODE": "test"},
        "deployment_status": Defenders.DeploymentStatus.SUCCESSFUL,
    }
    values.update(overrides)
    return SimpleNamespace(**values)


def make_query(defender=None) -> Mock:
    query = Mock()
    query.afirst = AsyncMock(return_value=defender)
    query.aupdate = AsyncMock(return_value=1)
    return query
