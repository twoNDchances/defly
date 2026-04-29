package schema

import (
	"time"

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
		field.Time("created_at").Default(time.Now),
		field.Time("updated_at").Default(time.Now).UpdateDefault(time.Now),
	}
}

func (Permission) Edges() []ent.Edge {
	return []ent.Edge{
		edge.From("users", User.Type).Ref("permissions"),
		edge.From("groups", Group.Type).Ref("permissions"),
	}
}
