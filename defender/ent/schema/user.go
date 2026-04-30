package schema

import (
	"entgo.io/ent"
	"entgo.io/ent/schema/edge"
	"entgo.io/ent/schema/field"
	"github.com/google/uuid"
)

type User struct {
	ent.Schema
}

func (User) Fields() []ent.Field {
	return []ent.Field{
		field.UUID("id", uuid.UUID{}).Default(uuid.New),
		field.String("email").Unique().NotEmpty(),
		field.Bool("is_verified").Default(false),
		field.Bool("is_activated").Default(true),
		field.Bool("is_root").Default(false),
	}
}

func (User) Edges() []ent.Edge {
	return []ent.Edge{
		edge.To("groups", Group.Type).
			StorageKey(
				edge.Table("users_groups"),
				edge.Columns("user", "group"),
			),
		edge.To("permissions", Permission.Type).
			StorageKey(
				edge.Table("users_permissions"),
				edge.Columns("user", "permission"),
			),
	}
}
