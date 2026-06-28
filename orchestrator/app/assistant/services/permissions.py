from django.core.exceptions import PermissionDenied

from app.bases.models import User
from app.bases.services.permissions import PermissionService


class ResourcePermissionService:
    RESOURCE_MODELS = {
        "user": "User",
        "group": "Group",
        "permission": "Permission",
        "key": "Key",
        "label": "Label",
        "wordlist": "Wordlist",
        "engine": "Engine",
        "pattern": "Pattern",
        "target": "Target",
        "action": "Action",
        "rule": "Rule",
        "principle": "Principle",
        "decision": "Decision",
        "defender": "Defender",
        "timeline": "Timeline",
    }

    @classmethod
    async def validate_messages(
        cls,
        *,
        messages: list[dict[str, object]],
        user: User | None,
    ) -> None:
        for message in messages:
            resources = message.get("resources")
            if resources is None:
                continue
            if not isinstance(resources, list):
                raise PermissionDenied("Invalid attached resource data.")

            for resource in resources:
                await cls.validate_resource(resource=resource, user=user)

    @classmethod
    async def validate_resource(
        cls,
        *,
        resource: object,
        user: User | None,
    ) -> None:
        if not isinstance(resource, dict):
            raise PermissionDenied("Invalid attached resource data.")

        resource_type = str(resource.get("type", "")).strip().lower()
        model = cls.RESOURCE_MODELS.get(resource_type)
        if model is None:
            raise PermissionDenied("Unsupported attached resource type.")

        if not await PermissionService.acan(
            user=user,
            model=model,
            action="viewAny",
        ):
            raise PermissionDenied("Attached resource list permission denied.")
        if not await PermissionService.acan(
            user=user,
            model=model,
            action="view",
        ):
            raise PermissionDenied("Attached resource view permission denied.")
