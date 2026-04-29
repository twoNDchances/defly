package schema

import (
	"time"

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
		field.Text("description").Optional().Nillable(),
		field.UUID("created_by", uuid.UUID{}).Optional().Nillable(),
		field.Bool("is_locked").Default(false),
		field.Time("created_at").Default(time.Now),
		field.Time("updated_at").Default(time.Now).UpdateDefault(time.Now),
	}
}

func (Action) Edges() []ent.Edge {
	return []ent.Edge{
		edge.From("creator", User.Type).Ref("created_actions").Field("created_by").Unique(),
		edge.To("rules", Rule.Type).
			StorageKey(
				edge.Table("rules_actions"),
				edge.Columns("action", "rule"),
			),
	}
}
