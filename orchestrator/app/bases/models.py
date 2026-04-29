from django.db.models import (
    BooleanField,
    CharField,
    ForeignKey,
    ManyToManyField,
    Model,
    UUIDField,
)
from django.db.models.deletion import CASCADE


class Permission(Model):
    id = UUIDField(primary_key=True, editable=False)
    applied_for = CharField(max_length=255)
    action = CharField(max_length=255)

    class Meta:
        managed = False
        db_table = "permissions"


class Group(Model):
    id = UUIDField(primary_key=True, editable=False)
    permissions = ManyToManyField(
        "Permission",
        through="GroupPermission",
        through_fields=("group", "permission"),
        related_name="groups",
    )

    class Meta:
        managed = False
        db_table = "groups"


class User(Model):
    id = UUIDField(primary_key=True, editable=False)
    email = CharField(max_length=255, unique=True)
    is_verified = BooleanField(default=False)
    is_root = BooleanField(default=False)
    is_activated = BooleanField(default=True)
    groups = ManyToManyField(
        "Group",
        through="UserGroup",
        through_fields=("user", "group"),
        related_name="users",
    )
    permissions = ManyToManyField(
        "Permission",
        through="UserPermission",
        through_fields=("user", "permission"),
        related_name="users",
    )

    class Meta:
        managed = False
        db_table = "users"


class UserPermission(Model):
    user = ForeignKey(
        User,
        on_delete=CASCADE,
        db_column="user",
        related_name="users_permissions",
    )
    permission = ForeignKey(
        Permission,
        on_delete=CASCADE,
        db_column="permission",
        related_name="users_permissions",
    )

    class Meta:
        managed = False
        db_table = "users_permissions"
        unique_together = (("user", "permission"),)


class UserGroup(Model):
    user = ForeignKey(
        User,
        on_delete=CASCADE,
        db_column="user",
        related_name="users_groups",
    )
    group = ForeignKey(
        Group,
        on_delete=CASCADE,
        db_column="group",
        related_name="users_groups",
    )

    class Meta:
        managed = False
        db_table = "users_groups"
        unique_together = (("user", "group"),)


class GroupPermission(Model):
    group = ForeignKey(
        Group,
        on_delete=CASCADE,
        db_column="group",
        related_name="groups_permissions",
    )
    permission = ForeignKey(
        Permission,
        on_delete=CASCADE,
        db_column="permission",
        related_name="groups_permissions",
    )

    class Meta:
        managed = False
        db_table = "groups_permissions"
        unique_together = (("group", "permission"),)
