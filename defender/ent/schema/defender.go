package schema

import (
	"entgo.io/ent"
	"entgo.io/ent/schema/edge"
	"entgo.io/ent/schema/field"
	"github.com/google/uuid"
)

type Defender struct {
	ent.Schema
}

func (Defender) Fields() []ent.Field {
	return []ent.Field{
		field.UUID("id", uuid.UUID{}).Default(uuid.New),
		field.String("name").Unique().NotEmpty(),
		field.Enum("status").Values("normal", "abnormal").Optional().Nillable(),
		field.JSON("details", map[string]any{}).Optional(),
	}
}

func (Defender) Edges() []ent.Edge {
	return []ent.Edge{
		edge.To("principles", Principle.Type).
			StorageKey(
				edge.Table("defenders_principles"),
				edge.Columns("defender", "principle"),
			),
		edge.To("decisions", Decision.Type).
			StorageKey(
				edge.Table("defenders_decisions"),
				edge.Columns("defender", "decision"),
			),
		edge.To("reports", Report.Type),
	}
}
