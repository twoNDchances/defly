package schema

import (
	"entgo.io/ent"
	"entgo.io/ent/schema/edge"
	"entgo.io/ent/schema/field"
	"github.com/google/uuid"
)

type Action struct {
	ent.Schema
}

func (Action) Fields() []ent.Field {
	return []ent.Field{
		field.UUID("id", uuid.UUID{}).Default(uuid.New),
		field.String("name").Unique().NotEmpty(),
		field.Enum("type").Values(
			"allow",
			"deny",
			"log",
			"request",
			"report",
			"suspect",
			"setter",
			"score",
			"level",
		),
		field.JSON("configurations", map[string]any{}).Optional(),
	}
}

func (Action) Edges() []ent.Edge {
	return []ent.Edge{
		edge.To("rules", Rule.Type).
			StorageKey(
				edge.Table("rules_actions"),
				edge.Columns("action", "rule"),
			),
		edge.To("reports", Report.Type),
	}
}
