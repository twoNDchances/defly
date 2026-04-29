package schema

import (
	"time"

	"entgo.io/ent"
	"entgo.io/ent/schema/edge"
	"entgo.io/ent/schema/field"
	"github.com/google/uuid"
)

type Wordlist struct {
	ent.Schema
}

func (Wordlist) Fields() []ent.Field {
	return []ent.Field{
		field.UUID("id", uuid.UUID{}).Default(uuid.New),
		field.String("name").Unique().NotEmpty(),
		field.Enum("type").Values("file", "json"),
		field.Text("word_file").Optional().Nillable(),
		field.JSON("word_json", []string{}).Optional(),
		field.Int("word_count").Optional().Nillable(),
		field.Text("description").Optional().Nillable(),
		field.UUID("created_by", uuid.UUID{}).Optional().Nillable(),
		field.Bool("is_locked").Default(false),
		field.Time("created_at").Default(time.Now),
		field.Time("updated_at").Default(time.Now).UpdateDefault(time.Now),
	}
}

func (Wordlist) Edges() []ent.Edge {
	return []ent.Edge{
		edge.From("creator", User.Type).Ref("created_wordlists").Field("created_by").Unique(),
		edge.To("targets", Target.Type),
		edge.To("rules", Rule.Type),
	}
}
