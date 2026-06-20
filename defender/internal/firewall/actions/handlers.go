package actions

import (
	"encoding/json"
	"errors"
	"fmt"
	"io"
	"log"
	"net"
	"net/http"
	"net/url"
	"os"
	"strconv"
	"strings"
	"time"
)

const logTimeFormat = "02/01/2006 15:04:05"

type Action interface {
	Execute(tx Transaction)
	Async() bool
	Validate() error
}

type Transaction interface {
	IsAllowed() bool
	IsDenied() bool
	SetAllow()
	SetDeny(status int, contentType string, body []byte)
	AddScore(score float64)
	CurrentScore() float64
	SetScore(score float64)
	CurrentLevel() int
	SetLevel(level int)
	SetVar(key string, value any)
	UnsetVar(key string)
	AwaitReportReady(timeout time.Duration) bool
	RawRequest() []byte
	RawResponse() []byte
	RequestRemoteAddr() string
	RequestHeaders() http.Header
	RequestQuery() url.Values
	RequestBodyBytes() []byte
	RequestMethod() string
	RequestPath() string
	RequestScheme() string
	RequestProto() string
	RequestHost() string
	RequestURL() string
	RequestFullURLValue() string
	RequestPort() float64
	RequestContentLength() int64
	RequestContentType() string
	ResponseHeaders() http.Header
	ResponseBodyBytes() []byte
	ResponseStatusCode() int
	ResponseProto() string
	ResponseContentLength() int64
	ResponseContentType() string
	VarValue(key string) (any, bool)
}

type Allow struct{}

func (Allow) Execute(tx Transaction) {
	tx.SetAllow()
}

func (Allow) Async() bool {
	return false
}

func (Allow) Validate() error {
	return nil
}

type Deny struct {
	Status      int
	ContentType string
	Body        []byte
}

func (d Deny) Execute(tx Transaction) {
	tx.SetDeny(d.Status, d.ContentType, d.Body)
}

func (d Deny) Async() bool {
	return false
}

func (d Deny) Validate() error {
	errs := make([]error, 0)
	if http.StatusText(d.Status) == "" {
		errs = append(errs, fmt.Errorf("%d status is not valid", d.Status))
	}
	if d.ContentType == "" {
		errs = append(errs, errors.New("content type is empty"))
	}
	if strings.Contains(d.ContentType, "json") && !json.Valid(d.Body) {
		errs = append(errs, errors.New("body is not valid JSON"))
	}
	return errors.Join(errs...)
}

type Suspect struct {
	Score float64
}

func (s Suspect) Execute(tx Transaction) {
	tx.AddScore(s.Score)
}

func (s Suspect) Async() bool {
	return false
}

func (s Suspect) Validate() error {
	return nil
}

type Score struct {
	Value    float64
	Operator string
}

func (s Score) Execute(tx Transaction) {
	tx.SetScore(applyBehavior(tx.CurrentScore(), s.Value, s.Operator))
}

func (s Score) Async() bool {
	return false
}

func (s Score) Validate() error {
	return nil
}

type Level struct {
	Value    float64
	Operator string
}

func (l Level) Execute(tx Transaction) {
	next := applyBehavior(float64(tx.CurrentLevel()), l.Value, l.Operator)
	if next < 1 {
		next = 1
	}
	tx.SetLevel(int(next))
}

func (l Level) Async() bool {
	return false
}

func (l Level) Validate() error {
	return nil
}

type Setter struct {
	Directive string
	Items     []SetterItem
}

type SetterItem struct {
	Key   string
	Value any
}

func (s Setter) Execute(tx Transaction) {
	for _, item := range s.Items {
		if s.Directive == "unset" {
			tx.UnsetVar(item.Key)
			continue
		}
		tx.SetVar(item.Key, item.Value)
	}
}

func (s Setter) Async() bool {
	return false
}

func (s Setter) Validate() error {
	return nil
}

type Log struct {
	Render func(tx Transaction) string
	Path   string
}

func (l Log) Execute(tx Transaction) {
	line := l.Render(tx)
	if l.Path == "" {
		log.Println(line)
		return
	}
	file, err := os.OpenFile(l.Path, os.O_CREATE|os.O_APPEND|os.O_WRONLY, 0644)
	if err != nil {
		log.Println(err)
		return
	}
	defer file.Close()
	_, _ = file.WriteString(line + "\n")
}

func (l Log) Async() bool {
	return false
}

func (l Log) Validate() error {
	return nil
}

type Request struct {
	Send func()
}

func (r Request) Execute(tx Transaction) {
	if r.Send != nil {
		r.Send()
	}
}

func (r Request) Async() bool {
	return true
}

func (r Request) Validate() error {
	return nil
}

func renderLog(tx Transaction, format string) string {
	return renderLogFormat(format, func(tag string) (string, bool) {
		lowerTag := strings.ToLower(tag)
		switch lowerTag {
		case "pid":
			return strconv.Itoa(os.Getpid()), true
		case "time":
			return time.Now().Format(logTimeFormat), true
		case "referer":
			return tx.RequestHeaders().Get("Referer"), true
		case "ip":
			return logClientIP(tx), true
		case "ips":
			ips := strings.TrimSpace(tx.RequestHeaders().Get("X-Forwarded-For"))
			if ips == "" {
				return logClientIP(tx), true
			}
			return ips, true
		case "method":
			return stringify(tx.RequestMethod()), true
		case "path", "route":
			return stringify(tx.RequestPath()), true
		case "score":
			return floatString(tx.CurrentScore()), true
		case "protocol":
			return stringify(tx.RequestProto()), true
		case "host":
			return stringify(tx.RequestHost()), true
		case "url":
			return stringify(tx.RequestURL()), true
		case "ua":
			return tx.RequestHeaders().Get("User-Agent"), true
		case "status":
			return intString(tx.ResponseStatusCode()), true
		case "resbody":
			return string(tx.ResponseBodyBytes()), true
		case "reqheaders":
			return serializeHeaders(tx.RequestHeaders()), true
		case "queryparams":
			return tx.RequestQuery().Encode(), true
		case "body":
			return string(tx.RequestBodyBytes()), true
		case "bytessent":
			return int64String(tx.ResponseContentLength()), true
		case "bytesreceived":
			return int64String(tx.RequestContentLength()), true
		case "from":
			return "WAF", true
		case "port":
			return extractPort(tx.RequestRemoteAddr()), true
		}
		if parameter, ok := extractTagParameter(tag, lowerTag, "reqheader:"); ok {
			return tx.RequestHeaders().Get(parameter), true
		}
		if parameter, ok := extractTagParameter(tag, lowerTag, "respheader:"); ok {
			return tx.ResponseHeaders().Get(parameter), true
		}
		if parameter, ok := extractTagParameter(tag, lowerTag, "query:"); ok {
			return tx.RequestQuery().Get(parameter), true
		}
		if parameter, ok := extractTagParameter(tag, lowerTag, "locals:"); ok {
			value, exists := tx.VarValue(parameter)
			if !exists {
				return "", true
			}
			return stringify(value), true
		}
		return "", false
	})
}

func renderLogFormat(format string, resolver func(tag string) (string, bool)) string {
	var builder strings.Builder
	builder.Grow(len(format) + 64)
	for index := 0; index < len(format); {
		if format[index] != '%' {
			builder.WriteByte(format[index])
			index++
			continue
		}
		closingOffset := strings.IndexByte(format[index+1:], '%')
		if closingOffset < 0 {
			builder.WriteByte(format[index])
			index++
			continue
		}
		closingIndex := index + 1 + closingOffset
		tag := format[index+1 : closingIndex]
		if tag == "" {
			builder.WriteByte('%')
			index = closingIndex + 1
			continue
		}
		if value, ok := resolver(tag); ok {
			builder.WriteString(value)
		} else {
			builder.WriteString(format[index : closingIndex+1])
		}
		index = closingIndex + 1
	}
	return builder.String()
}

func sendRequest(config map[string]any, client *http.Client) {
	requestURL := stringConfig(config, "url", "")
	if requestURL == "" {
		return
	}
	method := strings.ToUpper(stringConfig(config, "method", http.MethodGet))
	bodyText := stringConfig(config, "body", "")
	requestURL = urlWithBodyQuery(requestURL, method, bodyText)
	var body io.Reader
	if method != http.MethodGet {
		body = strings.NewReader(bodyText)
	}
	request, err := http.NewRequest(method, requestURL, body)
	if err != nil {
		log.Println(err)
		return
	}
	for key, value := range keyValueMap(config, "headers") {
		request.Header.Set(key, value)
	}
	if client == nil {
		client = &http.Client{Timeout: 5 * time.Second}
	}
	response, err := client.Do(request)
	if err != nil {
		log.Println(err)
		return
	}
	_, _ = io.Copy(io.Discard, response.Body)
	_ = response.Body.Close()
}

func urlWithBodyQuery(requestURL string, method string, body string) string {
	if method != http.MethodGet || strings.TrimSpace(body) == "" {
		return requestURL
	}
	parsedURL, err := url.Parse(requestURL)
	if err != nil {
		return requestURL
	}
	bodyQuery, err := url.ParseQuery(body)
	if err != nil {
		return requestURL
	}
	query := parsedURL.Query()
	for key, values := range bodyQuery {
		for _, value := range values {
			query.Add(key, value)
		}
	}
	parsedURL.RawQuery = query.Encode()
	return parsedURL.String()
}

func logClientIP(tx Transaction) string {
	address := strings.TrimSpace(tx.RequestRemoteAddr())
	host, _, err := net.SplitHostPort(address)
	if err == nil {
		return strings.Trim(host, "[]")
	}
	return strings.Trim(address, "[]")
}

func extractPort(address string) string {
	_, port, err := net.SplitHostPort(strings.TrimSpace(address))
	if err != nil {
		return ""
	}
	return port
}

func extractTagParameter(tag, lowerTag, prefix string) (string, bool) {
	if !strings.HasPrefix(lowerTag, prefix) {
		return "", false
	}
	separatorIndex := strings.Index(tag, ":")
	if separatorIndex < 0 || separatorIndex == len(tag)-1 {
		return "", true
	}
	return strings.TrimSpace(tag[separatorIndex+1:]), true
}

func serializeHeaders(header http.Header) string {
	if len(header) == 0 {
		return ""
	}
	serializedHeader, err := json.Marshal(header)
	if err != nil {
		return stringify(header)
	}
	return string(serializedHeader)
}

func intString(value int) string {
	return strconv.Itoa(value)
}

func int64String(value int64) string {
	if value < 0 {
		value = 0
	}
	return strconv.FormatInt(value, 10)
}

func floatString(value float64) string {
	return strconv.FormatFloat(value, 'f', -1, 64)
}
