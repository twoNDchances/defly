package request

import (
	"log"
	"net/http"
	"net/url"

	decisionaction "defly-defender/internal/waf/decisions/action"
)

type Transaction interface {
	ResultState() *decisionaction.Result
	RequestObject() *http.Request
	SetRequestBody(body []byte)
}

type Applier struct{}

func (a Applier) Apply(tx Transaction, writer http.ResponseWriter) bool {
	if tx == nil || tx.ResultState() == nil {
		return false
	}
	result := tx.ResultState()
	a.applyMutation(tx)
	if result.Cancel {
		if conn, _, err := http.NewResponseController(writer).Hijack(); err == nil {
			_ = conn.Close()
		} else {
			log.Println("waf cancel decision could not close connection:", err)
		}
		return true
	}
	if !result.Deny {
		return false
	}
	status := result.Status
	if status == 0 {
		status = http.StatusForbidden
	}
	contentType := result.ContentType
	if contentType == "" {
		contentType = "application/json"
	}
	body := result.Body
	if len(body) == 0 {
		body = []byte(`{"message":"request denied"}`)
	}
	writer.Header().Set("Content-Type", contentType)
	writer.WriteHeader(status)
	_, _ = writer.Write(body)
	return true
}

func (Applier) applyMutation(tx Transaction) {
	request := tx.RequestObject()
	if request == nil {
		return
	}
	result := tx.ResultState()
	if result.RewritePath != "" {
		request.URL.Path = result.RewritePath
	}
	if len(result.RewriteQuery) > 0 || len(result.UnsetQuery) > 0 {
		query := request.URL.Query()
		for key, value := range result.RewriteQuery {
			query.Set(key, value)
		}
		for _, key := range result.UnsetQuery {
			query.Del(key)
		}
		request.URL.RawQuery = query.Encode()
	}
	for key, values := range result.RewriteHeaders {
		request.Header.Del(key)
		for _, value := range values {
			request.Header.Add(key, value)
		}
	}
	for _, key := range result.UnsetHeaders {
		request.Header.Del(key)
	}
	if result.BodyRewritten {
		tx.SetRequestBody(result.BodyRewrite)
	}
	if result.RedirectURL != "" {
		if parsed, err := url.Parse(result.RedirectURL); err == nil {
			request.URL = parsed
			request.Host = parsed.Host
		}
	}
}
