package engines

import (
	"testing"

	"defly-defender/ent"
	entengine "defly-defender/ent/engine"
	enttarget "defly-defender/ent/target"
)

func TestTransformerRunsEngineChain(t *testing.T) {
	target := &ent.Target{
		Datatype: enttarget.DatatypeString,
		Edges: ent.TargetEdges{
			Engines: []*ent.Engine{
				{
					Name:           "trim",
					Type:           "trim",
					InputDatatype:  entengine.InputDatatypeString,
					OutputDatatype: entengine.OutputDatatypeString,
				},
				{
					Name:           "lower",
					Type:           "lower",
					InputDatatype:  entengine.InputDatatypeString,
					OutputDatatype: entengine.OutputDatatypeString,
				},
			},
		},
	}

	got := Transformer{}.TransformTarget("  ADMIN  ", target)
	if got != "admin" {
		t.Fatalf("TransformTarget() = %#v, want admin", got)
	}
}

func TestTransformerStopsWhenDatatypeDoesNotMatch(t *testing.T) {
	target := &ent.Target{
		Datatype: enttarget.DatatypeString,
		Edges: ent.TargetEdges{
			Engines: []*ent.Engine{
				{
					Name:           "addition",
					Type:           "addition",
					InputDatatype:  entengine.InputDatatypeNumber,
					OutputDatatype: entengine.OutputDatatypeNumber,
				},
			},
		},
	}

	got := Transformer{}.TransformTarget("7", target)
	if got != "7" {
		t.Fatalf("TransformTarget() = %#v, want 7 as string", got)
	}
}
