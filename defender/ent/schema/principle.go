package schema

import (
	"entgo.io/ent"
	"entgo.io/ent/dialect/entsql"
	"entgo.io/ent/schema"
	"entgo.io/ent/schema/edge"
	"entgo.io/ent/schema/field"
	"github.com/google/uuid"
)

type Principle struct {
	ent.Schema
}

func (Principle) Fields() []ent.Field {
	return []ent.Field{
		field.UUID("id", uuid.UUID{}).Default(uuid.New),
		field.String("name").Unique().NotEmpty(),
		field.Uint64("level").Default(1),
		field.Int("phase"),
		field.Bool("is_applied").Default(false),
	}
}

func (Principle) Edges() []ent.Edge {
	return []ent.Edge{
		edge.From("rules", Rule.Type).Ref("principles"),
		edge.From("defenders", Defender.Type).Ref("principles"),
	}
}

func (Principle) Annotations() []schema.Annotation {
	return []schema.Annotation{
		entsql.Annotation{Table: "principles"},
	}
}
