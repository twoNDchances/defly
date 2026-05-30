package firewall

import (
	"io"
	"net/http"
	"net/http/httptest"
	"strings"
	"testing"
)

func TestNewTransactionCapturesAndRestoresRequestBody(t *testing.T) {
	request := httptest.NewRequest(http.MethodPost, "https://example.test/login?next=/", strings.NewReader("username=admin"))
	request.Header.Set("Content-Type", "application/x-www-form-urlencoded")

	tx, err := New(Runtime{}).NewTransaction(request)
	if err != nil {
		t.Fatalf("NewTransaction() error = %v", err)
	}

	if string(tx.RequestBodyBytes()) != "username=admin" {
		t.Fatalf("RequestBodyBytes() = %q", tx.RequestBodyBytes())
	}
	body, err := io.ReadAll(request.Body)
	if err != nil {
		t.Fatalf("ReadAll(request.Body) error = %v", err)
	}
	if string(body) != "username=admin" {
		t.Fatalf("restored body = %q", body)
	}
	if tx.RequestFullURLValue() != "https://example.test/login?next=/" {
		t.Fatalf("full URL = %q", tx.RequestFullURLValue())
	}
}

func TestCaptureResponseRestoresResponseBody(t *testing.T) {
	tx := New(Runtime{}).NewBlankTransaction(httptest.NewRequest(http.MethodGet, "http://example.test/", nil))
	response := &http.Response{
		StatusCode:    http.StatusOK,
		Status:        "200 OK",
		Proto:         "HTTP/1.1",
		ProtoMajor:    1,
		ProtoMinor:    1,
		Header:        http.Header{"Content-Type": []string{"text/plain"}},
		Body:          io.NopCloser(strings.NewReader("hello")),
		ContentLength: 5,
		Request:       tx.RequestObject(),
	}

	if err := tx.CaptureResponse(response); err != nil {
		t.Fatalf("CaptureResponse() error = %v", err)
	}
	if string(tx.ResponseBodyBytes()) != "hello" {
		t.Fatalf("ResponseBodyBytes() = %q", tx.ResponseBodyBytes())
	}
	body, err := io.ReadAll(response.Body)
	if err != nil {
		t.Fatalf("ReadAll(response.Body) error = %v", err)
	}
	if string(body) != "hello" {
		t.Fatalf("restored response body = %q", body)
	}
}
