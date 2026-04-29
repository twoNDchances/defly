package schema

import (
	"time"

	"entgo.io/ent"
	"entgo.io/ent/schema/edge"
	"entgo.io/ent/schema/field"
	"github.com/google/uuid"
)

type User struct {
	ent.Schema
}

func (User) Fields() []ent.Field {
	return []ent.Field{
		field.UUID("id", uuid.UUID{}).Default(uuid.New),
		field.String("email").Unique().NotEmpty(),
		field.Bool("is_verified").Default(false),
		field.Bool("is_activated").Default(true),
		field.Bool("is_root").Default(false),
		field.Time("created_at").Default(time.Now),
		field.Time("updated_at").Default(time.Now).UpdateDefault(time.Now),
	}
}

func (User) Edges() []ent.Edge {
	return []ent.Edge{
		edge.To("groups", Group.Type),
		edge.To("permissions", Permission.Type),
		edge.To("created_wordlists", Wordlist.Type),
		edge.To("created_engines", Engine.Type),
		edge.To("created_targets", Target.Type),
		edge.To("created_actions", Action.Type),
		edge.To("created_rules", Rule.Type),
		edge.To("created_principles", Principle.Type),
		edge.To("created_decisions", Decision.Type),
	}
}
