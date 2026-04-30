package schema

import (
	"entgo.io/ent"
	"entgo.io/ent/schema/edge"
	"entgo.io/ent/schema/field"
	"github.com/google/uuid"
)

type Engine struct {
	ent.Schema
}

func (Engine) Fields() []ent.Field {
	return []ent.Field{
		field.UUID("id", uuid.UUID{}).Default(uuid.New),
		field.String("name").Unique().NotEmpty(),
		field.Enum("input_datatype").Values("array", "number", "string"),
		field.String("type").NotEmpty(),
		field.JSON("configurations", map[string]any{}).Optional(),
		field.Enum("output_datatype").Values("array", "number", "string"),
	}
}

func (Engine) Edges() []ent.Edge {
	return []ent.Edge{
		edge.To("targets", Target.Type).
			StorageKey(
				edge.Table("targets_engines"),
				edge.Columns("engine", "target"),
			),
	}
}
