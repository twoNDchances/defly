package decisions

import (
	"fmt"
	"log"
	"net/http"
	"net/url"
)

type Applier struct{}

func (Applier) ApplyRequest(tx interface {
	Transaction
	SetRequestBody(body []byte)
}, writer http.ResponseWriter) bool {
	if tx == nil || tx.ResultState() == nil {
		return false
	}
	result := tx.ResultState()
	applyRequestMutation(tx)
	if result.Cancel {
		if conn, _, err := http.NewResponseController(writer).Hijack(); err == nil {
			_ = conn.Close()
		} else {
			log.Println("firewall cancel decision could not close connection:", err)
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

func (Applier) ApplyResponse(tx interface {
	Transaction
	SetResponseBody(body []byte)
}) error {
	if tx == nil || tx.ResultState() == nil || tx.ResponseObject() == nil {
		return nil
	}
	result := tx.ResultState()
	response := tx.ResponseObject()
	if result.Deny {
		status := result.Status
		if status == 0 {
			status = http.StatusForbidden
		}
		response.StatusCode = status
		response.Status = fmt.Sprintf("%d %s", status, http.StatusText(status))
		response.Header = http.Header{}
		contentType := result.ContentType
		if contentType == "" {
			contentType = "application/json"
		}
		response.Header.Set("Content-Type", contentType)
		body := result.Body
		if len(body) == 0 {
			body = []byte(`{"message":"response denied"}`)
		}
		tx.SetResponseBody(body)
		return nil
	}
	for key, values := range result.RewriteHeaders {
		response.Header.Del(key)
		for _, value := range values {
			response.Header.Add(key, value)
		}
	}
	for _, key := range result.UnsetHeaders {
		response.Header.Del(key)
	}
	if result.ForceNoCache {
		response.Header.Set("Cache-Control", "no-store, no-cache, must-revalidate, max-age=0")
		response.Header.Set("Pragma", "no-cache")
		response.Header.Set("Expires", "0")
	}
	if result.EraseCookies {
		response.Header.Del("Set-Cookie")
	}
	if result.BodyRewritten {
		tx.SetResponseBody(result.BodyRewrite)
	}
	return nil
}

func applyRequestMutation(tx interface {
	Transaction
	SetRequestBody(body []byte)
}) {
	request := tx.RequestObject()
	if request == nil || tx.ResultState() == nil {
		return
	}
	result := tx.ResultState()
	if result.RewritePath != "" && request.URL != nil {
		request.URL.Path = result.RewritePath
	}
	if request.URL != nil && (len(result.RewriteQuery) > 0 || len(result.UnsetQuery) > 0) {
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
