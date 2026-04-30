package schema

import (
	"entgo.io/ent"
	"entgo.io/ent/schema/edge"
	"entgo.io/ent/schema/field"
	"github.com/google/uuid"
)

type Permission struct {
	ent.Schema
}

func (Permission) Fields() []ent.Field {
	return []ent.Field{
		field.UUID("id", uuid.UUID{}).Default(uuid.New),
		field.String("applied_for").NotEmpty(),
		field.String("action").NotEmpty(),
	}
}

func (Permission) Edges() []ent.Edge {
	return []ent.Edge{
		edge.From("users", User.Type).Ref("permissions"),
		edge.From("groups", Group.Type).Ref("permissions"),
	}
}
