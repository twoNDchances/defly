package schema

import (
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
	}
}

func (Wordlist) Edges() []ent.Edge {
	return []ent.Edge{
		edge.To("targets", Target.Type),
		edge.To("rules", Rule.Type),
	}
}
