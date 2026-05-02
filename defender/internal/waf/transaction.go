package waf

import (
	"bytes"
	"io"
	"net/http"
)

type Config struct {
	ViolationScore int
	ViolationLevel int
	Severity       map[string]int
}

type DecisionResult struct {
	Allow                 bool
	Deny                  bool
	Cancel                bool
	StopRequestDecisions  bool
	StopResponseDecisions bool
	Status                int
	ContentType           string
	Body                  []byte
	BodyRewrite           []byte
	BodyRewritten         bool
	RewriteHeaders        http.Header
	UnsetHeaders          []string
	RewritePath           string
	RewriteQuery          map[string]string
	UnsetQuery            []string
	RedirectURL           string
	ForceNoCache          bool
	EraseCookies          bool
}

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
	Result        DecisionResult
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
