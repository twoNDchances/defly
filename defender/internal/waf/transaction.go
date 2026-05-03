package waf

import (
	"bytes"
	"io"
	"net/http"
	"net/url"
	"strconv"

	decisionaction "defly-defender/internal/waf/decisions/action"
)

type Config struct {
	ViolationScore int
	ViolationLevel int
	Severity       map[string]int
}

type DecisionResult = decisionaction.Result

type Transaction struct {
	Request       *http.Request
	Response      *http.Response
	RequestRaw    []byte
	RequestBody   []byte
	ResponseRaw   []byte
	ResponseBody  []byte
	Score         float64
	Level         int
	Vars          map[string]any
	Result        decisionaction.Result
	phaseContexts map[int]map[string]any
}

func (tx *Transaction) CaptureRequest() error {
	if tx.Request == nil {
		return nil
	}

	if tx.Request.Body != nil {
		body, err := io.ReadAll(tx.Request.Body)
		if err != nil {
			return err
		}
		tx.RequestBody = body
		tx.Request.Body = io.NopCloser(bytes.NewReader(body))
		tx.Request.ContentLength = int64(len(body))
	}

	var raw bytes.Buffer
	if err := tx.Request.Write(&raw); err != nil {
		return err
	}
	tx.RequestRaw = raw.Bytes()
	tx.Request.Body = io.NopCloser(bytes.NewReader(tx.RequestBody))
	tx.Request.ContentLength = int64(len(tx.RequestBody))
	return nil
}

func (tx *Transaction) CaptureResponse(response *http.Response) error {
	tx.Response = response
	if response == nil {
		return nil
	}

	if response.Body != nil {
		body, err := io.ReadAll(response.Body)
		if err != nil {
			return err
		}
		tx.ResponseBody = body
		response.Body = io.NopCloser(bytes.NewReader(body))
		response.ContentLength = int64(len(body))
	}

	var raw bytes.Buffer
	if err := response.Write(&raw); err != nil {
		return err
	}
	tx.ResponseRaw = raw.Bytes()
	response.Body = io.NopCloser(bytes.NewReader(tx.ResponseBody))
	response.ContentLength = int64(len(tx.ResponseBody))
	return nil
}

func (tx *Transaction) SetResponseBody(body []byte) {
	if tx.Response == nil {
		return
	}
	tx.ResponseBody = body
	tx.Response.Body = io.NopCloser(bytes.NewReader(body))
	tx.Response.ContentLength = int64(len(body))
	tx.Response.Header.Set("Content-Length", tx.stringInt(len(body)))
}

func (tx *Transaction) SetRequestBody(body []byte) {
	if tx.Request == nil {
		return
	}
	tx.RequestBody = body
	tx.Request.Body = io.NopCloser(bytes.NewReader(body))
	tx.Request.ContentLength = int64(len(body))
	tx.Request.Header.Set("Content-Length", tx.stringInt(len(body)))
}

func (tx *Transaction) SetAllow() {
	tx.Result.Allow = true
}

func (tx *Transaction) IsAllowed() bool {
	return tx != nil && tx.Result.Allow
}

func (tx *Transaction) SetDeny(status int, contentType string, body []byte) {
	tx.Result.Deny = true
	tx.Result.Status = status
	tx.Result.ContentType = contentType
	tx.Result.Body = body
}

func (tx *Transaction) IsDenied() bool {
	return tx != nil && tx.Result.Deny
}

func (tx *Transaction) AddScore(score float64) {
	tx.Score += score
}

func (tx *Transaction) CurrentScore() float64 {
	return tx.Score
}

func (tx *Transaction) SetScore(score float64) {
	tx.Score = score
}

func (tx *Transaction) CurrentLevel() int {
	return tx.Level
}

func (tx *Transaction) SetLevel(level int) {
	tx.Level = level
}

func (tx *Transaction) SetVar(key string, value any) {
	tx.Vars[key] = value
}

func (tx *Transaction) UnsetVar(key string) {
	delete(tx.Vars, key)
}

func (tx *Transaction) ResultState() *decisionaction.Result {
	if tx == nil {
		return nil
	}
	return &tx.Result
}

func (tx *Transaction) ScoreValue() float64 {
	if tx == nil {
		return 0
	}
	return tx.Score
}

func (tx *Transaction) LevelValue() int {
	if tx == nil {
		return 0
	}
	return tx.Level
}

func (tx *Transaction) RequestObject() *http.Request {
	if tx == nil {
		return nil
	}
	return tx.Request
}

func (tx *Transaction) ResponseObject() *http.Response {
	if tx == nil {
		return nil
	}
	return tx.Response
}

func (tx *Transaction) RawRequest() []byte {
	if tx == nil {
		return nil
	}
	return tx.RequestRaw
}

func (tx *Transaction) RawResponse() []byte {
	if tx == nil {
		return nil
	}
	return tx.ResponseRaw
}

func (tx *Transaction) RequestBodyBytes() []byte {
	if tx == nil {
		return nil
	}
	return tx.RequestBody
}

func (tx *Transaction) ResponseBodyBytes() []byte {
	if tx == nil {
		return nil
	}
	return tx.ResponseBody
}

func (tx *Transaction) RequestHeaders() http.Header {
	if tx == nil || tx.Request == nil {
		return nil
	}
	return tx.Request.Header
}

func (tx *Transaction) ResponseHeaders() http.Header {
	if tx == nil || tx.Response == nil {
		return nil
	}
	return tx.Response.Header
}

func (tx *Transaction) RequestQuery() url.Values {
	if tx == nil || tx.Request == nil {
		return nil
	}
	return tx.Request.URL.Query()
}

func (tx *Transaction) RequestMethod() string {
	if tx == nil || tx.Request == nil {
		return ""
	}
	return tx.Request.Method
}

func (tx *Transaction) RequestProto() string {
	if tx == nil || tx.Request == nil {
		return ""
	}
	return tx.Request.Proto
}

func (tx *Transaction) RequestURL() string {
	if tx == nil || tx.Request == nil {
		return ""
	}
	return tx.Request.URL.String()
}

func (tx *Transaction) RequestRemoteAddr() string {
	if tx == nil || tx.Request == nil {
		return ""
	}
	return tx.Request.RemoteAddr
}

func (tx *Transaction) RequestPath() string {
	if tx == nil || tx.Request == nil {
		return ""
	}
	return tx.Request.URL.Path
}

func (tx *Transaction) RequestScheme() string {
	if tx == nil || tx.Request == nil {
		return ""
	}
	if tx.Request.URL.Scheme != "" {
		return tx.Request.URL.Scheme
	}
	if tx.Request.TLS != nil {
		return "https"
	}
	return "http"
}

func (tx *Transaction) RequestHost() string {
	if tx == nil || tx.Request == nil {
		return ""
	}
	return tx.Request.Host
}

func (tx *Transaction) RequestPort() float64 {
	if tx == nil || tx.Request == nil {
		return 0
	}
	port := tx.Request.URL.Port()
	if port == "" {
		if tx.Request.TLS != nil {
			return 443
		}
		return 80
	}
	value, _ := strconv.ParseFloat(port, 64)
	return value
}

func (tx *Transaction) RequestContentType() string {
	if tx == nil || tx.Request == nil {
		return ""
	}
	return tx.Request.Header.Get("Content-Type")
}

func (tx *Transaction) RequestContentLength() int64 {
	if tx == nil || tx.Request == nil {
		return 0
	}
	return tx.Request.ContentLength
}

func (tx *Transaction) ResponseStatusCode() int {
	if tx == nil || tx.Response == nil {
		return 0
	}
	return tx.Response.StatusCode
}

func (tx *Transaction) ResponseProto() string {
	if tx == nil || tx.Response == nil {
		return ""
	}
	return tx.Response.Proto
}

func (tx *Transaction) ResponseContentType() string {
	if tx == nil || tx.Response == nil {
		return ""
	}
	return tx.Response.Header.Get("Content-Type")
}

func (tx *Transaction) ResponseContentLength() int64 {
	if tx == nil || tx.Response == nil {
		return 0
	}
	return tx.Response.ContentLength
}

func (tx *Transaction) VarValue(key string) (any, bool) {
	if tx == nil {
		return nil, false
	}
	value, ok := tx.Vars[key]
	return value, ok
}

func (tx *Transaction) stringInt(value int) string {
	if value == 0 {
		return "0"
	}
	var buf [20]byte
	i := len(buf)
	for value > 0 {
		i--
		buf[i] = byte('0' + value%10)
		value /= 10
	}
	return string(buf[i:])
}
