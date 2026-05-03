package response

import (
	"fmt"
	"net/http"

	decisionaction "defly-defender/internal/waf/decisions/action"
)

type Transaction interface {
	ResultState() *decisionaction.Result
	ResponseObject() *http.Response
	SetResponseBody(body []byte)
}

type Applier struct{}

func (Applier) Apply(tx Transaction) error {
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
		response.Header.Set("Content-Type", result.ContentType)
		if result.ContentType == "" {
			response.Header.Set("Content-Type", "application/json")
		}
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
