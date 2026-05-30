package firewall

import (
	"net/http"
	"net/http/httptest"
	"testing"

	"defly-defender/ent"
	entaction "defly-defender/ent/action"
	enttarget "defly-defender/ent/target"
)

func TestCoreRunRequestExecutesMatchingRuleAction(t *testing.T) {
	target := &ent.Target{
		Name:     "q",
		Phase:    PhaseRequestBody,
		Type:     enttarget.TypeQuery,
		Datatype: enttarget.DatatypeString,
	}
	action := &ent.Action{
		Type: entaction.TypeDeny,
		Configurations: map[string]any{
			"status":       418,
			"content_type": "json",
			"body":         `{"message":"blocked"}`,
		},
	}
	rule := &ent.Rule{
		Phase:          PhaseRequestBody,
		Comparator:     "@contains",
		Configurations: map[string]any{"value": "attack"},
		Edges: ent.RuleEdges{
			Target:  target,
			Actions: []*ent.Action{action},
		},
	}
	principle := &ent.Principle{
		Phase: PhaseRequestBody,
		Level: 1,
		Edges: ent.PrincipleEdges{
			Rules: []*ent.Rule{rule},
		},
	}
	core := New(Runtime{Principles: []*ent.Principle{principle}})
	request := httptest.NewRequest(http.MethodGet, "http://example.test/?q=attack", nil)
	tx, err := core.NewTransaction(request)
	if err != nil {
		t.Fatalf("NewTransaction() error = %v", err)
	}

	core.RunRequest(tx)

	if !tx.IsDenied() {
		t.Fatal("transaction was not denied")
	}
	recorder := httptest.NewRecorder()
	if !core.ApplyRequest(tx, recorder) {
		t.Fatal("ApplyRequest() = false, want true")
	}
	if recorder.Code != 418 {
		t.Fatalf("status = %d, want 418", recorder.Code)
	}
	if recorder.Body.String() != `{"message":"blocked"}` {
		t.Fatalf("body = %q", recorder.Body.String())
	}
}
