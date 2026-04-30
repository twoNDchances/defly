package schema

import (
	"time"

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
		field.Enum("validation_status").Values("pending", "validating", "failed", "passed").Optional().Nillable(),
		field.JSON("validation_details", map[string]any{}).Optional(),
		field.Text("description").Optional().Nillable(),
		field.UUID("created_by", uuid.UUID{}).Optional().Nillable(),
		field.Bool("is_locked").Default(false),
		field.Time("created_at").Default(time.Now),
		field.Time("updated_at").Default(time.Now).UpdateDefault(time.Now),
	}
}

func (Principle) Edges() []ent.Edge {
	return []ent.Edge{
		edge.From("creator", User.Type).Ref("created_principles").Field("created_by").Unique(),
		edge.From("rules", Rule.Type).Ref("principles"),
		edge.From("defenders", Defender.Type).Ref("principles"),
	}
}

func (Principle) Annotations() []schema.Annotation {
	return []schema.Annotation{
		entsql.Annotation{Table: "principles"},
	}
}
