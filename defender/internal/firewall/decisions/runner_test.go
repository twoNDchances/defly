package decisions

import (
	"net/http"
	"os"
	"path/filepath"
	"strings"
	"testing"

	entdecision "defly-defender/ent/decision"
	"defly-defender/internal/firewall/state"
)

func TestRunnerSaveDecisionWritesRawRequest(t *testing.T) {
	directory := t.TempDir()
	rawRequest := []byte("GET /admin HTTP/1.1\r\nHost: example.test\r\n\r\n")
	tx := &testTransaction{
		score:      10,
		rawRequest: rawRequest,
	}

	Runner{
		Decisions: []Decision{
			testDecision{
				direction: entdecision.DirectionRequest,
				condition: entdecision.ConditionGreaterThanOrEqual,
				action:    entdecision.ActionSave,
				score:     5,
				configurations: map[string]any{
					"directory": directory,
					"position":  "prefix",
					"name":      "blocked/request",
				},
			},
		},
	}.Run(tx, entdecision.DirectionRequest)

	entries, err := os.ReadDir(directory)
	if err != nil {
		t.Fatalf("ReadDir() error = %v", err)
	}
	if len(entries) != 1 {
		t.Fatalf("saved files = %d, want 1", len(entries))
	}
	filename := entries[0].Name()
	if !strings.HasPrefix(filename, "blocked_request-") || !strings.HasSuffix(filename, ".http") {
		t.Fatalf("filename = %q, want sanitized prefixed .http filename", filename)
	}
	content, err := os.ReadFile(filepath.Join(directory, filename))
	if err != nil {
		t.Fatalf("ReadFile() error = %v", err)
	}
	if string(content) != string(rawRequest) {
		t.Fatalf("saved request = %q, want %q", string(content), string(rawRequest))
	}
}

type testDecision struct {
	direction      entdecision.Direction
	condition      entdecision.Condition
	action         entdecision.Action
	score          float64
	configurations map[string]any
}

func (d testDecision) DirectionValue() entdecision.Direction {
	return d.direction
}

func (d testDecision) ConditionValue() entdecision.Condition {
	return d.condition
}

func (d testDecision) ActionValue() entdecision.Action {
	return d.action
}

func (d testDecision) ScoreValue() float64 {
	return d.score
}

func (d testDecision) ConfigurationsValue() map[string]any {
	return d.configurations
}

type testTransaction struct {
	result       state.Result
	score        float64
	rawRequest   []byte
	request      *http.Request
	response     *http.Response
	responseBody []byte
}

func (tx *testTransaction) ResultState() *state.Result {
	return &tx.result
}

func (tx *testTransaction) ScoreValue() float64 {
	return tx.score
}

func (tx *testTransaction) LevelValue() int {
	return 1
}

func (tx *testTransaction) RequestObject() *http.Request {
	return tx.request
}

func (tx *testTransaction) ResponseObject() *http.Response {
	return tx.response
}

func (tx *testTransaction) RawRequest() []byte {
	return tx.rawRequest
}

func (tx *testTransaction) RequestBodyBytes() []byte {
	return nil
}

func (tx *testTransaction) ResponseBodyBytes() []byte {
	return nil
}

func (tx *testTransaction) RequestContentType() string {
	return ""
}

func (tx *testTransaction) ResponseContentType() string {
	return ""
}

func (tx *testTransaction) SetResponseBody(body []byte) {
	tx.responseBody = body
}
