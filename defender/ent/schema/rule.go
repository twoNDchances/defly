package schema

import (
	"time"

	"entgo.io/ent"
	"entgo.io/ent/schema/edge"
	"entgo.io/ent/schema/field"
	"github.com/google/uuid"
)

type Rule struct {
	ent.Schema
}

func (Rule) Fields() []ent.Field {
	return []ent.Field{
		field.UUID("id", uuid.UUID{}).Default(uuid.New),
		field.String("name").Unique().NotEmpty(),
		field.Int("phase"),
		field.UUID("target_id", uuid.UUID{}).Optional().Nillable(),
		field.String("comparator").NotEmpty(),
		field.Bool("is_inversed").Default(false),
		field.JSON("configurations", map[string]any{}).Optional(),
		field.UUID("wordlist_id", uuid.UUID{}).Optional().Nillable(),
		field.Text("description").Optional().Nillable(),
		field.UUID("created_by", uuid.UUID{}).Optional().Nillable(),
		field.Bool("is_locked").Default(false),
		field.Time("created_at").Default(time.Now),
		field.Time("updated_at").Default(time.Now).UpdateDefault(time.Now),
	}
}

func (Rule) Edges() []ent.Edge {
	return []ent.Edge{
		edge.From("target", Target.Type).Ref("rules").Field("target_id").Unique(),
		edge.From("wordlist", Wordlist.Type).Ref("rules").Field("wordlist_id").Unique(),
		edge.From("creator", User.Type).Ref("created_rules").Field("created_by").Unique(),
		edge.From("actions", Action.Type).Ref("rules"),
		edge.To("principles", Principle.Type).
			StorageKey(
				edge.Table("principles_rules"),
				edge.Columns("rule", "principle"),
			),
	}
}
