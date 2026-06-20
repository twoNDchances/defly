package actions

import (
	"io"
	"net/http"
	"net/http/httptest"
	"net/url"
	"testing"
	"time"
)

func TestSendRequestConvertsGetBodyToQuery(t *testing.T) {
	type receivedRequest struct {
		method string
		query  string
		body   string
	}

	received := make(chan receivedRequest, 1)
	server := httptest.NewServer(http.HandlerFunc(func(_ http.ResponseWriter, request *http.Request) {
		body, _ := io.ReadAll(request.Body)
		received <- receivedRequest{
			method: request.Method,
			query:  request.URL.RawQuery,
			body:   string(body),
		}
	}))
	defer server.Close()

	sendRequest(map[string]any{
		"url":    server.URL + "/hook?existing=1",
		"method": http.MethodGet,
		"body":   "username=admin&password=secret",
	}, server.Client())

	select {
	case request := <-received:
		if request.method != http.MethodGet {
			t.Fatalf("method = %s, want GET", request.method)
		}
		if request.body != "" {
			t.Fatalf("body = %q, want empty body", request.body)
		}
		values, err := url.ParseQuery(request.query)
		if err != nil {
			t.Fatalf("query parse error = %v", err)
		}
		if values.Get("existing") != "1" || values.Get("username") != "admin" || values.Get("password") != "secret" {
			t.Fatalf("query = %q, want existing username and password", request.query)
		}
	case <-time.After(time.Second):
		t.Fatal("request was not sent")
	}
}
