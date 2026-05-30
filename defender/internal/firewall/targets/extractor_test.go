package targets_test

import (
	"bytes"
	"io"
	"mime/multipart"
	"net/http"
	"net/http/httptest"
	"reflect"
	"strings"
	"testing"

	"defly-defender/ent"
	enttarget "defly-defender/ent/target"
	"defly-defender/internal/firewall"
	"defly-defender/internal/firewall/targets"
)

func TestExtractorExtractsRequestTargets(t *testing.T) {
	request := httptest.NewRequest(http.MethodPost, "http://example.test/search?q=admin", strings.NewReader(`{"user":{"name":"root"}}`))
	request.Header.Set("Content-Type", "application/json")
	request.Header.Set("X-Test", "header-value")
	tx, err := firewall.New(firewall.Runtime{}).NewTransaction(request)
	if err != nil {
		t.Fatalf("NewTransaction() error = %v", err)
	}

	extractor := targets.Extractor{}
	tests := []struct {
		name   string
		target *ent.Target
		want   any
	}{
		{
			name:   "header",
			target: &ent.Target{Name: "X-Test", Phase: 3, Type: enttarget.TypeHeader, Datatype: enttarget.DatatypeString},
			want:   "header-value",
		},
		{
			name:   "query",
			target: &ent.Target{Name: "q", Phase: 3, Type: enttarget.TypeQuery, Datatype: enttarget.DatatypeString},
			want:   "admin",
		},
		{
			name:   "json body dotted",
			target: &ent.Target{Name: "user.name", Phase: 3, Type: enttarget.TypeBody, Datatype: enttarget.DatatypeString},
			want:   "root",
		},
		{
			name:   "meta method",
			target: &ent.Target{Name: "method", Phase: 3, Type: enttarget.TypeMeta, Datatype: enttarget.DatatypeString},
			want:   http.MethodPost,
		},
	}

	for _, tt := range tests {
		t.Run(tt.name, func(t *testing.T) {
			if got := extractor.Extract(tx, tt.target, 3); got != tt.want {
				t.Fatalf("Extract() = %#v, want %#v", got, tt.want)
			}
		})
	}
}

func TestExtractorExtractsResponseBody(t *testing.T) {
	tx := firewall.New(firewall.Runtime{}).NewBlankTransaction(httptest.NewRequest(http.MethodGet, "http://example.test/", nil))
	response := &http.Response{
		StatusCode:    http.StatusOK,
		Status:        "200 OK",
		Proto:         "HTTP/1.1",
		ProtoMajor:    1,
		ProtoMinor:    1,
		Header:        http.Header{"Content-Type": []string{"application/json"}},
		Body:          io.NopCloser(strings.NewReader(`{"ok":true}`)),
		ContentLength: int64(len(`{"ok":true}`)),
		Request:       tx.RequestObject(),
	}
	if err := tx.CaptureResponse(response); err != nil {
		t.Fatalf("CaptureResponse() error = %v", err)
	}

	target := &ent.Target{Name: "ok", Phase: 5, Type: enttarget.TypeBody, Datatype: enttarget.DatatypeString}
	if got := (targets.Extractor{}).Extract(tx, target, 5); got != true {
		t.Fatalf("Extract() = %#v, want true", got)
	}
}

func TestExtractPatternSeparatesBodySizeAndLength(t *testing.T) {
	body := "username=alice&password=secret"
	request := httptest.NewRequest(http.MethodPost, "http://example.test/login", strings.NewReader(body))
	request.Header.Set("Content-Type", "application/x-www-form-urlencoded")
	tx, err := firewall.New(firewall.Runtime{}).NewTransaction(request)
	if err != nil {
		t.Fatalf("NewTransaction() error = %v", err)
	}

	assertNumber(t, targets.ExtractPattern("request-body-size", tx), 2)
	assertNumber(t, targets.ExtractPattern("request-body-length", tx), float64(len(body)))

	responseBody := `{"ok":true,"status":"done"}`
	response := &http.Response{
		StatusCode:    http.StatusOK,
		Status:        "200 OK",
		Proto:         "HTTP/1.1",
		ProtoMajor:    1,
		ProtoMinor:    1,
		Header:        http.Header{"Content-Type": []string{"application/json"}},
		Body:          io.NopCloser(strings.NewReader(responseBody)),
		ContentLength: int64(len(responseBody)),
		Request:       tx.RequestObject(),
	}
	if err := tx.CaptureResponse(response); err != nil {
		t.Fatalf("CaptureResponse() error = %v", err)
	}

	assertNumber(t, targets.ExtractPattern("response-body-size", tx), 2)
	assertNumber(t, targets.ExtractPattern("response-body-length", tx), float64(len(responseBody)))
}

func TestExtractPatternSeparatesFileSizeAndLength(t *testing.T) {
	var body bytes.Buffer
	writer := multipart.NewWriter(&body)
	first, err := writer.CreateFormFile("attachments", "first.txt")
	if err != nil {
		t.Fatalf("CreateFormFile() error = %v", err)
	}
	if _, err := first.Write([]byte("abc")); err != nil {
		t.Fatalf("Write() error = %v", err)
	}
	second, err := writer.CreateFormFile("attachments", "second.txt")
	if err != nil {
		t.Fatalf("CreateFormFile() error = %v", err)
	}
	if _, err := second.Write([]byte("12345")); err != nil {
		t.Fatalf("Write() error = %v", err)
	}
	if err := writer.Close(); err != nil {
		t.Fatalf("Close() error = %v", err)
	}

	request := httptest.NewRequest(http.MethodPost, "http://example.test/upload", &body)
	request.Header.Set("Content-Type", writer.FormDataContentType())
	tx, err := firewall.New(firewall.Runtime{}).NewTransaction(request)
	if err != nil {
		t.Fatalf("NewTransaction() error = %v", err)
	}

	assertNumber(t, targets.ExtractPattern("request-file-size", tx), 2)
	assertNumber(t, targets.ExtractPattern("request-file-name-size", tx), 2)
	assertNumber(t, targets.ExtractPattern("request-file-length", tx), 8)
}

func TestExtractPatternDetectsFileExtensionFromSignature(t *testing.T) {
	var body bytes.Buffer
	writer := multipart.NewWriter(&body)
	part, err := writer.CreateFormFile("upload", "payload.php")
	if err != nil {
		t.Fatalf("CreateFormFile() error = %v", err)
	}
	png := []byte{
		0x89, 0x50, 0x4e, 0x47, 0x0d, 0x0a, 0x1a, 0x0a,
		0x00, 0x00, 0x00, 0x0d, 0x49, 0x48, 0x44, 0x52,
		0x00, 0x00, 0x00, 0x01, 0x00, 0x00, 0x00, 0x01,
		0x08, 0x02, 0x00, 0x00, 0x00, 0x90, 0x77, 0x53,
		0xde,
	}
	if _, err := part.Write(png); err != nil {
		t.Fatalf("Write() error = %v", err)
	}
	if err := writer.Close(); err != nil {
		t.Fatalf("Close() error = %v", err)
	}

	request := httptest.NewRequest(http.MethodPost, "http://example.test/upload", &body)
	request.Header.Set("Content-Type", writer.FormDataContentType())
	tx, err := firewall.New(firewall.Runtime{}).NewTransaction(request)
	if err != nil {
		t.Fatalf("NewTransaction() error = %v", err)
	}

	filenameExtensions, ok := targets.ExtractPattern("request-file-extensions", tx).([]string)
	if !ok {
		t.Fatalf("request-file-extensions type = %T, want []string", filenameExtensions)
	}
	if want := []string{"php"}; !reflect.DeepEqual(filenameExtensions, want) {
		t.Fatalf("request-file-extensions = %#v, want %#v", filenameExtensions, want)
	}

	detectedExtensions, ok := targets.ExtractPattern("request-file-detected-extensions", tx).([]string)
	if !ok {
		t.Fatalf("request-file-detected-extensions type = %T, want []string", detectedExtensions)
	}
	if want := []string{"png"}; !reflect.DeepEqual(detectedExtensions, want) {
		t.Fatalf("request-file-detected-extensions = %#v, want %#v", detectedExtensions, want)
	}
}

func assertNumber(t *testing.T, got any, want float64) {
	t.Helper()

	number, ok := got.(float64)
	if !ok {
		t.Fatalf("value type = %T, want float64", got)
	}
	if number != want {
		t.Fatalf("value = %v, want %v", number, want)
	}
}
