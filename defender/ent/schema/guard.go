package schema

import (
	"entgo.io/ent"
	"entgo.io/ent/schema/edge"
	"entgo.io/ent/schema/field"
	"github.com/google/uuid"
)

type Guard struct {
	ent.Schema
}

func (Guard) Fields() []ent.Field {
	return []ent.Field{
		field.UUID("id", uuid.UUID{}).Default(uuid.New),
		field.String("name").Unique().NotEmpty(),
		field.Time("expired_at").Optional().Nillable(),
	}
}

func (Guard) Edges() []ent.Edge {
	return []ent.Edge{
		edge.From("users", User.Type).Ref("guards"),
		edge.From("defenders", Defender.Type).Ref("guards"),
	}
}
