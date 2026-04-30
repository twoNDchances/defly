package schema

import (
	"entgo.io/ent"
	"entgo.io/ent/schema/edge"
	"entgo.io/ent/schema/field"
	"github.com/google/uuid"
)

type Pattern struct {
	ent.Schema
}

func (Pattern) Fields() []ent.Field {
	return []ent.Field{
		field.UUID("id", uuid.UUID{}).Default(uuid.New),
		field.String("name").Unique().NotEmpty(),
		field.Int("phase"),
		field.Enum("type").Values("full", "header", "meta", "query", "body", "file"),
		field.Enum("datatype").Values("array", "number", "string"),
	}
}

func (Pattern) Edges() []ent.Edge {
	return []ent.Edge{
		edge.To("targets", Target.Type),
	}
}
