package actions

import (
	"bytes"
	"context"
	"encoding/json"
	"errors"
	"log"
	"mime"
	"mime/multipart"
	"net/http"
	"net/url"
	"strings"
	"time"

	"defly-defender/ent"
	ruleengines "defly-defender/internal/firewall/engines"
	ruleruntime "defly-defender/internal/firewall/rules"
	ruletargets "defly-defender/internal/firewall/targets"
	"defly-defender/internal/firewall/wordlists"
	_ "github.com/go-sql-driver/mysql"
	"github.com/google/uuid"
)

const fullExpectedValuesLimit = 10

type Report struct {
	DatabaseDSN  string
	DefenderID   string
	ActionID     string
	Rule         *ent.Rule
	RuleDetails  map[string]any
	ReadyTimeout time.Duration
}

func (r Report) Execute(tx Transaction) {
	if strings.TrimSpace(r.DatabaseDSN) == "" {
		return
	}
	timeout := r.ReadyTimeout
	if timeout <= 0 {
		timeout = 2 * time.Minute
	}
	r.RuleDetails = ensureMap(r.RuleDetails, r.ruleDetails(tx))
	tx.AwaitReportReady(timeout)
	if err := r.insert(tx, r.RuleDetails); err != nil {
		log.Println("firewall report action could not create report:", err)
	}
}

func (Report) Async() bool {
	return true
}

func (r Report) Validate() error {
	if strings.TrimSpace(r.DatabaseDSN) == "" {
		return errors.New("report database DSN is empty")
	}
	return nil
}

func (r Report) insert(tx Transaction, ruleDetails map[string]any) error {
	client, err := ent.Open("mysql", r.DatabaseDSN)
	if err != nil {
		return err
	}
	defer client.Close()

	ctx, cancel := context.WithTimeout(context.Background(), 3*time.Second)
	defer cancel()

	return client.Report.Create().
		SetMetas(reportMetas(tx)).
		SetRequestHeaders(reportHeaders(tx.RequestHeaders())).
		SetRequestBody(reportRequestBody(tx)).
		SetResponseHeaders(reportHeaders(tx.ResponseHeaders())).
		SetResponseBody(reportResponseBody(tx)).
		SetRuleDetails(ruleDetails).
		SetNillableTriggeredBy(parseUUID(r.ActionID)).
		SetNillableCreatedBy(parseUUID(r.DefenderID)).
		Exec(ctx)
}

func reportMetas(tx Transaction) map[string]any {
	return map[string]any{
		"ip":       logClientIP(tx),
		"url":      reportFullURL(tx),
		"status":   tx.ResponseStatusCode(),
		"method":   tx.RequestMethod(),
		"protocol": tx.RequestProto(),
	}
}

func reportFullURL(tx Transaction) string {
	if fullURL := strings.TrimSpace(tx.RequestFullURLValue()); fullURL != "" {
		return fullURL
	}

	rawURL := strings.TrimSpace(tx.RequestURL())
	if strings.HasPrefix(rawURL, "http://") || strings.HasPrefix(rawURL, "https://") {
		return rawURL
	}

	host := strings.TrimSpace(tx.RequestHost())
	if host == "" {
		return rawURL
	}

	scheme := strings.TrimSpace(tx.RequestScheme())
	if scheme == "" {
		scheme = "http"
	}
	if rawURL == "" {
		rawURL = "/"
	}
	if !strings.HasPrefix(rawURL, "/") {
		rawURL = "/" + rawURL
	}
	return scheme + "://" + host + rawURL
}

func reportHeaders(headers http.Header) []map[string]string {
	items := make([]map[string]string, 0, len(headers))
	for key, values := range headers {
		items = append(items, map[string]string{
			"key":   key,
			"value": strings.Join(values, "; "),
		})
	}
	return items
}

func reportRequestBody(tx Transaction) map[string]any {
	return jsonBody(tx.RequestBodyBytes(), tx.RequestContentType(), true)
}

func reportResponseBody(tx Transaction) map[string]any {
	contentType := strings.ToLower(tx.ResponseContentType())
	if isJSONContentType(contentType) {
		return jsonBody(tx.ResponseBodyBytes(), contentType, false)
	}
	if len(tx.ResponseBodyBytes()) == 0 {
		return map[string]any{}
	}
	return map[string]any{
		responseBodyKey(contentType): string(tx.ResponseBodyBytes()),
	}
}

func jsonBody(body []byte, contentType string, parseNonJSON bool) map[string]any {
	if len(body) == 0 {
		return map[string]any{}
	}

	mediaType, params, _ := mime.ParseMediaType(contentType)
	if mediaType == "" {
		mediaType = strings.ToLower(contentType)
	}

	if isJSONContentType(mediaType) {
		var decoded any
		if err := json.Unmarshal(body, &decoded); err == nil {
			if mapped, ok := decoded.(map[string]any); ok {
				return mapped
			}
			return map[string]any{
				"body": decoded,
			}
		}
	}
	if !parseNonJSON {
		return map[string]any{
			responseBodyKey(mediaType): string(body),
		}
	}

	switch mediaType {
	case "application/x-www-form-urlencoded":
		values, err := url.ParseQuery(string(body))
		if err == nil {
			return valuesToMap(values)
		}
	case "multipart/form-data":
		return multipartBody(body, params["boundary"])
	}

	return map[string]any{
		"body": string(body),
	}
}

func multipartBody(body []byte, boundary string) map[string]any {
	result := map[string]any{
		"fields": map[string]any{},
		"files":  map[string]any{},
	}
	if boundary == "" {
		result["body"] = string(body)
		return result
	}

	fields := result["fields"].(map[string]any)
	files := result["files"].(map[string]any)
	reader := multipart.NewReader(bytes.NewReader(body), boundary)
	for {
		part, err := reader.NextPart()
		if err != nil {
			break
		}
		name := part.FormName()
		if name == "" {
			continue
		}
		content, _ := ioReadAll(part)
		if part.FileName() == "" {
			fields[name] = string(content)
			continue
		}
		files[name] = appendAny(files[name], map[string]any{
			"filename": part.FileName(),
			"size":     len(content),
			"content":  string(content),
		})
	}
	return result
}

func (r Report) ruleDetails(tx Transaction) map[string]any {
	rule := r.Rule
	if rule == nil {
		return map[string]any{}
	}

	target := rule.Edges.Target
	rawTarget := any(nil)
	trace := ruleengines.TransformTrace{}
	if target != nil {
		rawTarget = (ruletargets.Extractor{Wordlist: wordlists.Loader{}}).Extract(tx, target, rule.Phase)
		trace = (ruleengines.Transformer{}).TraceTarget(rawTarget, target)
	}

	expected := reportExpectedValues(rule)
	matchedExpected := ruleruntime.MatchedExpectedValues(rule.Comparator, trace.FinalValue, expected)
	matchedTarget := ruleruntime.MatchedTargetValues(rule.Comparator, trace.FinalValue, expected)

	return map[string]any{
		"rule": map[string]any{
			"id":          rule.ID.String(),
			"name":        rule.Name,
			"phase":       rule.Phase,
			"is_inversed": rule.IsInversed,
		},
		"target":          reportTargetDetails(target),
		"target_output":   rawTarget,
		"engine_chain":    trace.Steps,
		"final_output":    trace.FinalValue,
		"datatype":        trace.FinalDatatype,
		"comparator":      rule.Comparator,
		"expected_values": reportDisplayExpectedValues(expected, matchedExpected),
		"matched_values": map[string]any{
			"target":   matchedTarget,
			"expected": matchedExpected,
		},
		"matched_context": map[string]any{
			"target":   contextualMatch(toAnySlice(trace.FinalValue), matchedTarget),
			"expected": contextualMatch(expected, matchedExpected),
		},
	}
}

func reportTargetDetails(target *ent.Target) map[string]any {
	if target == nil {
		return map[string]any{}
	}

	details := map[string]any{
		"id":       target.ID.String(),
		"name":     target.Name,
		"phase":    target.Phase,
		"type":     target.Type.String(),
		"datatype": target.Datatype.String(),
	}
	if target.Edges.Pattern != nil {
		details["pattern"] = map[string]any{
			"id":       target.Edges.Pattern.ID.String(),
			"name":     target.Edges.Pattern.Name,
			"type":     target.Edges.Pattern.Type.String(),
			"datatype": target.Edges.Pattern.Datatype.String(),
		}
	}
	if target.Edges.Wordlist != nil {
		details["wordlist"] = map[string]any{
			"id":   target.Edges.Wordlist.ID.String(),
			"name": target.Edges.Wordlist.Name,
		}
	}
	return details
}

func reportExpectedValues(rule *ent.Rule) []any {
	if rule == nil {
		return nil
	}
	expected := make([]any, 0)
	for _, key := range []string{"number", "number_from", "number_to", "string", "value", "expected", "needle", "pattern", "min", "max"} {
		if value, ok := rule.Configurations[key]; ok {
			expected = append(expected, value)
		}
	}
	if rule.Edges.Wordlist != nil {
		for _, word := range (wordlists.Loader{}).Words(rule.Edges.Wordlist) {
			expected = append(expected, word)
		}
	}
	return expected
}

func reportDisplayExpectedValues(expected []any, matches []any) []any {
	if len(expected) <= fullExpectedValuesLimit {
		return expected
	}

	display := make([]any, 0, len(matches)+2)
	display = append(display, "...")
	display = append(display, matches...)
	display = append(display, "...")
	return display
}

func contextualMatch(values []any, matches []any) []any {
	if len(values) == 0 || len(matches) == 0 {
		return nil
	}

	context := make([]any, 0, len(matches)+2)
	lastMatchedIndex := -1
	for index, value := range values {
		if containsString(matches, stringify(value)) {
			if len(context) == 0 {
				if index > 0 {
					context = append(context, "...")
				}
			} else if index > lastMatchedIndex+1 {
				context = append(context, "...")
			}
			context = append(context, value)
			lastMatchedIndex = index
		}
	}
	if len(context) == 0 {
		return nil
	}
	if lastMatchedIndex < len(values)-1 {
		context = append(context, "...")
	}
	return context
}

func containsString(values []any, needle string) bool {
	for _, value := range values {
		if stringify(value) == needle {
			return true
		}
	}
	return false
}

func valuesToMap(values url.Values) map[string]any {
	result := make(map[string]any, len(values))
	for key, item := range values {
		if len(item) == 1 {
			result[key] = item[0]
			continue
		}
		result[key] = item
	}
	return result
}

func appendAny(value any, next any) []any {
	switch typed := value.(type) {
	case nil:
		return []any{next}
	case []any:
		return append(typed, next)
	default:
		return []any{typed, next}
	}
}

func isJSONContentType(contentType string) bool {
	contentType = strings.ToLower(contentType)
	return strings.Contains(contentType, "application/json") || strings.HasSuffix(contentType, "+json")
}

func responseBodyKey(contentType string) string {
	mediaType, _, _ := mime.ParseMediaType(strings.ToLower(contentType))
	if mediaType == "" {
		mediaType = strings.ToLower(contentType)
	}
	switch {
	case mediaType == "text/html" || mediaType == "application/xhtml+xml":
		return "html"
	case mediaType == "text/plain":
		return "text"
	case mediaType == "application/xml" || mediaType == "text/xml" || strings.HasSuffix(mediaType, "+xml"):
		return "xml"
	case strings.HasPrefix(mediaType, "image/"),
		strings.HasPrefix(mediaType, "audio/"),
		strings.HasPrefix(mediaType, "video/"),
		mediaType == "application/octet-stream",
		mediaType == "application/pdf",
		strings.Contains(mediaType, "zip"):
		return "file"
	default:
		return "body"
	}
}

func parseUUID(value string) *uuid.UUID {
	parsed, err := uuid.Parse(strings.TrimSpace(value))
	if err != nil {
		return nil
	}
	return &parsed
}

func ensureMap(primary map[string]any, fallback map[string]any) map[string]any {
	if primary != nil {
		return primary
	}
	return fallback
}

func ioReadAll(part *multipart.Part) ([]byte, error) {
	var buffer bytes.Buffer
	_, err := buffer.ReadFrom(part)
	return buffer.Bytes(), err
}
