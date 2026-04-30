package schema

import (
	"entgo.io/ent"
	"entgo.io/ent/schema/edge"
	"entgo.io/ent/schema/field"
	"github.com/google/uuid"
)

type Decision struct {
	ent.Schema
}

func (Decision) Fields() []ent.Field {
	return []ent.Field{
		field.UUID("id", uuid.UUID{}).Default(uuid.New),
		field.String("name").Unique().NotEmpty(),
		field.Enum("direction").Values("request", "response"),
		field.Enum("condition").NamedValues(
			"LessThanOrEqual", "<=",
			"LessThan", "<",
			"Equal", "=",
			"GreaterThanOrEqual", ">=",
			"GreaterThan", ">",
		),
		field.Float("score"),
		field.Enum("action").Values(
			"allow",
			"deny",
			"rewrite_headers",
			"rewrite_body",
			"redirect",
			"cancel",
			"rewrite",
			"save",
			"erase_cookies",
			"force_no_cache",
		),
		field.JSON("configurations", map[string]any{}).Optional(),
		field.Bool("is_implemented").Default(false),
	}
}

func (Decision) Edges() []ent.Edge {
	return []ent.Edge{
		edge.From("defenders", Defender.Type).Ref("decisions"),
	}
}
