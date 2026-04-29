package schema

import (
	"time"

	"entgo.io/ent"
	"entgo.io/ent/schema/edge"
	"entgo.io/ent/schema/field"
	"github.com/google/uuid"
)

type Target struct {
	ent.Schema
}

func (Target) Fields() []ent.Field {
	return []ent.Field{
		field.UUID("id", uuid.UUID{}).Default(uuid.New),
		field.String("name").Unique().NotEmpty(),
		field.Int("phase"),
		field.Enum("type").Values("getter", "full", "header", "meta", "query", "body", "file"),
		field.Enum("datatype").Values("array", "number", "string"),
		field.Text("description").Optional().Nillable(),
		field.UUID("pattern_id", uuid.UUID{}).Optional().Nillable(),
		field.UUID("wordlist_id", uuid.UUID{}).Optional().Nillable(),
		field.UUID("created_by", uuid.UUID{}).Optional().Nillable(),
		field.Bool("is_locked").Default(false),
		field.Time("created_at").Default(time.Now),
		field.Time("updated_at").Default(time.Now).UpdateDefault(time.Now),
	}
}

func (Target) Edges() []ent.Edge {
	return []ent.Edge{
		edge.From("pattern", Pattern.Type).Ref("targets").Field("pattern_id").Unique(),
		edge.From("wordlist", Wordlist.Type).Ref("targets").Field("wordlist_id").Unique(),
		edge.From("creator", User.Type).Ref("created_targets").Field("created_by").Unique(),
		edge.From("engines", Engine.Type).Ref("targets"),
		edge.To("rules", Rule.Type),
	}
}
