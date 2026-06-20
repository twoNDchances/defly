package decisions

import (
	"net/http"
	"strings"
	"testing"

	"defly-defender/internal/firewall/state"
)

func TestApplierEraseCookiesExpiresRequestCookies(t *testing.T) {
	request, err := http.NewRequest(http.MethodGet, "http://example.test/", nil)
	if err != nil {
		t.Fatalf("NewRequest() error = %v", err)
	}
	request.Header.Set("Cookie", "session=abc; theme=dark")
	response := &http.Response{
		StatusCode: http.StatusOK,
		Header:     http.Header{"Set-Cookie": {"old=value"}},
	}
	tx := &testTransaction{
		request:  request,
		response: response,
		result:   state.Result{EraseCookies: true},
	}

	if err := (Applier{}).ApplyResponse(tx); err != nil {
		t.Fatalf("ApplyResponse() error = %v", err)
	}

	cookies := response.Header.Values("Set-Cookie")
	if len(cookies) != 2 {
		t.Fatalf("Set-Cookie count = %d, want 2", len(cookies))
	}
	joined := strings.Join(cookies, "\n")
	if !strings.Contains(joined, "session=") || !strings.Contains(joined, "theme=") || !strings.Contains(joined, "Max-Age=0") {
		t.Fatalf("Set-Cookie = %q, want expired request cookies", joined)
	}
	if strings.Contains(joined, "old=value") {
		t.Fatalf("Set-Cookie = %q, should remove previous response cookies", joined)
	}
}
