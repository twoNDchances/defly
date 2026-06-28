from django.db.models import CharField, DateTimeField, JSONField, Model, TextField


class Message(Model):
    id = CharField(primary_key=True, editable=False, max_length=36)
    conservation_id = CharField(db_index=True, max_length=36)
    role = CharField(max_length=32)
    content = TextField()
    resources = JSONField(blank=True, null=True)
    created_at = DateTimeField(blank=True, null=True)
    updated_at = DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "messages"
