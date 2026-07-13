from django.db.models import (
    CharField,
    DateTimeField,
    ForeignKey,
    IntegerField,
    JSONField,
    ManyToManyField,
    Model,
    TextChoices,
    TextField,
    UUIDField,
)
from django.db.models.deletion import CASCADE


class Defenders(Model):
    class Status(TextChoices):
        NORMAL = "normal", "Normal"
        ABNORMAL = "abnormal", "Abnormal"

    class DeploymentStatus(TextChoices):
        PENDING = "pending", "Pending"
        PROCESSING = "processing", "Processing"
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
    deployment_details = JSONField(blank=True, null=True)
    description = TextField(blank=True, null=True)
    created_by = UUIDField(blank=True, null=True)
    created_at = DateTimeField(blank=True, null=True)
    updated_at = DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "defenders"


class Guard(Model):
    id = UUIDField(primary_key=True, editable=False)
    name = CharField(unique=True, db_index=True, max_length=255)
    expired_at = DateTimeField(blank=True, null=True)
    users = ManyToManyField(
        "bases.User",
        through="GuardUser",
        through_fields=("guard", "user"),
        related_name="guards",
    )
    defenders = ManyToManyField(
        "Defenders",
        through="GuardDefender",
        through_fields=("guard", "defender"),
        related_name="guards",
    )

    class Meta:
        managed = False
        db_table = "guards"


class GuardUser(Model):
    guard = ForeignKey(
        Guard,
        on_delete=CASCADE,
        db_column="guard",
        related_name="guards_users",
    )
    user = ForeignKey(
        "bases.User",
        on_delete=CASCADE,
        db_column="user",
        related_name="guards_users",
    )

    class Meta:
        managed = False
        db_table = "guards_users"
        unique_together = (("guard", "user"),)


class GuardDefender(Model):
    guard = ForeignKey(
        Guard,
        on_delete=CASCADE,
        db_column="guard",
        related_name="guards_defenders",
    )
    defender = ForeignKey(
        Defenders,
        on_delete=CASCADE,
        db_column="defender",
        related_name="guards_defenders",
    )

    class Meta:
        managed = False
        db_table = "guards_defenders"
        unique_together = (("guard", "defender"),)
