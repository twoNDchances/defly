package schema

import (
	"time"

	"entgo.io/ent"
	"entgo.io/ent/schema/edge"
	"entgo.io/ent/schema/field"
	"github.com/google/uuid"
)

type Report struct {
	ent.Schema
}

func (Report) Fields() []ent.Field {
	return []ent.Field{
		field.UUID("id", uuid.UUID{}).Default(uuid.New),
		field.JSON("metas", map[string]any{}).Optional(),
		field.JSON("request_headers", []map[string]string{}).Optional(),
		field.JSON("request_body", map[string]any{}).Optional(),
		field.JSON("response_headers", []map[string]string{}).Optional(),
		field.JSON("response_body", map[string]any{}).Optional(),
		field.JSON("rule_details", map[string]any{}).Optional(),
		field.UUID("triggered_by", uuid.UUID{}).Optional().Nillable(),
		field.UUID("created_by", uuid.UUID{}).Optional().Nillable(),
		field.Time("created_at").Default(time.Now).Immutable(),
		field.Time("updated_at").Default(time.Now).UpdateDefault(time.Now),
	}
}

func (Report) Edges() []ent.Edge {
	return []ent.Edge{
		edge.From("triggered_by_action", Action.Type).
			Ref("reports").
			Field("triggered_by").
			Unique(),
		edge.From("created_by_defender", Defender.Type).
			Ref("reports").
			Field("created_by").
			Unique(),
	}
}
