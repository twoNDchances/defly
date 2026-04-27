from django.db.models import (
    CharField,
    DateTimeField,
    IntegerField,
    JSONField,
    Model,
    TextChoices,
    TextField,
    UUIDField,
)


class Defenders(Model):
    class Status(TextChoices):
        NORMAL = "normal", "Normal"
        ABNORMAL = "abnormal", "Abnormal"

    class DeploymentStatus(TextChoices):
        PENDING = "pending", "Pending"
        DEPLOYING = "deploying", "Deploying"
        FAILED = "failed", "Failed"
        SUCCESSFUL = "successful", "Successful"

    id = UUIDField(primary_key=True, editable=False)
    name = CharField(unique=True, db_index=True, max_length=255)
    proxy_port = IntegerField(default=9948)
    environment_variables = JSONField()
    status = CharField(max_length=8, choices=Status.choices, blank=True, null=True)
    details = JSONField(blank=True, null=True)
    deployment_status = CharField(
        max_length=10, choices=DeploymentStatus.choices, blank=True, null=True
    )
    deployment_details = TextField(blank=True, null=True)
    description = TextField(blank=True, null=True)
    created_by = UUIDField(blank=True, null=True)
    created_at = DateTimeField(blank=True, null=True)
    updated_at = DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "defenders"
