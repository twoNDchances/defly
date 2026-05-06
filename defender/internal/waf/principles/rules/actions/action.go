package actions

import (
	"bytes"
	"encoding/json"
	"io"
	"log"
	"net"
	"net/http"
	"net/url"
	"os"
	"strconv"
	"strings"
	"time"

	"defly-defender/ent"
	entaction "defly-defender/ent/action"
	"defly-defender/internal/utilities"
)

const logTimeFormat = "02/01/2006 15:04:05"

var logColorTagValues = map[string]string{
	"black":   string(utilities.ColorBlack),
	"red":     string(utilities.ColorRed),
	"green":   string(utilities.ColorGreen),
	"yellow":  string(utilities.ColorYellow),
	"blue":    string(utilities.ColorBlue),
	"magenta": string(utilities.ColorMagenta),
	"cyan":    string(utilities.ColorCyan),
	"white":   string(utilities.ColorWhite),
	"reset":   string(utilities.ColorReset),
}

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

type Executor struct {
	Severity          map[string]int
	Client            *http.Client
	ReportDatabaseDSN string
	ReportDefenderID  string
	Rule              *ent.Rule
}

func (e Executor) Execute(tx Transaction, rule *ent.Rule, actions []*ent.Action) {
	e.Rule = rule
	for _, action := range actions {
		if action == nil || tx == nil || tx.IsAllowed() || tx.IsDenied() {
			return
		}
		runtimeAction, err := e.build(action)
		if err != nil {
			log.Println("waf action validation failed:", err)
		}
		if runtimeAction == nil {
			continue
		}
		if report, ok := runtimeAction.(Report); ok {
			report.RuleDetails = report.ruleDetails(tx)
			runtimeAction = report
		}
		if runtimeAction.Async() {
			go runtimeAction.Execute(tx)
			continue
		}
		runtimeAction.Execute(tx)
	}
}

func (e Executor) build(action *ent.Action) (Action, error) {
	cfg := action.Configurations
	var runtimeAction Action
	switch action.Type {
	case entaction.TypeAllow:
		runtimeAction = Allow{}
	case entaction.TypeDeny:
		runtimeAction = Deny{
			Status:      int(numberConfig(cfg, "status", http.StatusForbidden)),
			ContentType: denyContentType(cfg),
			Body:        []byte(stringConfig(cfg, "body", `{"message":"request denied"}`)),
		}
	case entaction.TypeLog:
		format := stringConfig(cfg, "format", "[%time%] %ip% | %method% | %path% | score=%score%")
		runtimeAction = Log{
			Render: func(tx Transaction) string {
				return renderLog(tx, format)
			},
			Path: logFilePath(cfg),
		}
	case entaction.TypeRequest:
		runtimeAction = Request{Send: func() { e.sendRequest(cfg) }}
	case entaction.TypeReport:
		runtimeAction = Report{
			DatabaseDSN: e.ReportDatabaseDSN,
			DefenderID:  e.ReportDefenderID,
			ActionID:    action.ID.String(),
			Rule:        e.Rule,
		}
	case entaction.TypeSuspect:
		runtimeAction = Suspect{Score: float64(e.Severity[stringConfig(cfg, "severity", "notice")])}
	case entaction.TypeSetter:
		runtimeAction = Setter{
			Directive: stringConfig(cfg, "directive", "set"),
			Items:     setterItems(cfg),
		}
	case entaction.TypeScore:
		runtimeAction = Score{Value: numberConfig(cfg, "value", 0), Operator: stringConfig(cfg, "operator", "override")}
	case entaction.TypeLevel:
		runtimeAction = Level{Value: numberConfig(cfg, "value", 1), Operator: stringConfig(cfg, "operator", "override")}
	default:
		return nil, nil
	}
	return runtimeAction, runtimeAction.Validate()
}

func (e Executor) sendRequest(config map[string]any) {
	requestURL := stringConfig(config, "url", "")
	if requestURL == "" {
		return
	}
	method := strings.ToUpper(stringConfig(config, "method", http.MethodGet))
	body := strings.NewReader(stringConfig(config, "body", ""))
	request, err := http.NewRequest(method, requestURL, body)
	if err != nil {
		log.Println(err)
		return
	}
	for key, value := range keyValueMap(config, "headers") {
		request.Header.Set(key, value)
	}
	client := e.Client
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

func setterItems(config map[string]any) []SetterItem {
	items := configItems(config, "execution")
	result := make([]SetterItem, 0, len(items))
	for _, item := range items {
		datatype := stringify(item["datatype"])
		result = append(result, SetterItem{
			Key:   stringify(item["key"]),
			Value: castDatatype(item["value"], datatype),
		})
	}
	return result
}

func renderLog(tx Transaction, format string) string {
	return renderLogFormat(format, func(tag string) (string, bool) {
		lowerTag := strings.ToLower(tag)
		if color, exists := logColorTagValues[lowerTag]; exists {
			return color, true
		}
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
		case "path":
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
		case "latency":
			return "", true
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
			return logBytesSent(tx), true
		case "bytesreceived":
			return logBytesReceived(tx), true
		case "route":
			return stringify(tx.RequestPath()), true
		case "error":
			return "", true
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
		if parameter, ok := extractTagParameter(tag, lowerTag, "form:"); ok {
			return parseLogFormValues(tx).Get(parameter), true
		}
		if parameter, ok := extractTagParameter(tag, lowerTag, "cookie:"); ok {
			return cookieValue(tx.RequestHeaders(), parameter), true
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

func firstStringConfig(config map[string]any, keys ...string) string {
	for _, key := range keys {
		if value, ok := config[key]; ok {
			text := strings.TrimSpace(stringify(value))
			if text != "" {
				return text
			}
		}
	}
	return ""
}

func logFilePath(config map[string]any) string {
	path := firstStringConfig(config, "path", "file_path", "filepath")
	if path != "" {
		return path
	}
	if value, ok := config["file"]; ok {
		if text := strings.TrimSpace(stringify(value)); text != "" && text != "true" && text != "false" {
			return text
		}
	}
	if boolConfig(config, "file", false) || boolConfig(config, "file_enable", false) || boolConfig(config, "file_enabled", false) {
		return "storage/logs/waf.log"
	}
	return ""
}

func boolConfig(config map[string]any, key string, fallback bool) bool {
	if config == nil {
		return fallback
	}
	value, ok := config[key]
	if !ok {
		return fallback
	}
	switch typed := value.(type) {
	case bool:
		return typed
	case string:
		parsed, err := strconv.ParseBool(strings.TrimSpace(typed))
		if err == nil {
			return parsed
		}
	}
	return fallback
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

func parseLogFormValues(tx Transaction) url.Values {
	values := url.Values{}
	requestBody := tx.RequestBodyBytes()
	contentType := strings.ToLower(tx.RequestContentType())
	if strings.Contains(contentType, "multipart/form-data") {
		request := &http.Request{
			Header:        tx.RequestHeaders().Clone(),
			Body:          io.NopCloser(bytes.NewBuffer(requestBody)),
			ContentLength: int64(len(requestBody)),
		}
		_ = request.ParseMultipartForm(32 << 20)
		if request.MultipartForm != nil {
			for key, formValues := range request.MultipartForm.Value {
				for _, value := range formValues {
					values.Add(key, value)
				}
			}
		}
		return values
	}
	if strings.Contains(contentType, "application/x-www-form-urlencoded") {
		parsed, err := url.ParseQuery(string(requestBody))
		if err == nil {
			return parsed
		}
	}
	return values
}

func cookieValue(header http.Header, name string) string {
	for _, line := range header.Values("Cookie") {
		for _, part := range strings.Split(line, ";") {
			key, value, ok := strings.Cut(strings.TrimSpace(part), "=")
			if ok && key == name {
				return value
			}
		}
	}
	return ""
}

func logBytesSent(tx Transaction) string {
	size := tx.ResponseContentLength()
	if size < 0 {
		size = int64(len(tx.ResponseBodyBytes()))
	}
	return int64String(size)
}

func logBytesReceived(tx Transaction) string {
	size := tx.RequestContentLength()
	if size < 0 {
		size = int64(len(tx.RequestBodyBytes()))
	}
	return int64String(size)
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
