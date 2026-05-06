package actions

import (
	"bytes"
	"context"
	"encoding/json"
	"errors"
	"log"
	"math"
	"mime"
	"mime/multipart"
	"net/http"
	"net/url"
	"regexp"
	"strings"
	"time"

	"defly-defender/ent"
	ruletargets "defly-defender/internal/waf/principles/rules/targets"
	ruleengines "defly-defender/internal/waf/principles/rules/targets/engines"
	"defly-defender/internal/waf/wordlist"
	_ "github.com/go-sql-driver/mysql"
	"github.com/google/uuid"
)

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
		log.Println("waf report action could not create report:", err)
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
		rawTarget = (ruletargets.Extractor{Wordlist: wordlist.Loader{}}).Extract(tx, target, rule.Phase)
		trace = (ruleengines.Transformer{}).TraceTarget(rawTarget, target)
	}

	expected := reportExpectedValues(rule)
	matchedExpected := matchedExpectedValues(rule.Comparator, trace.FinalValue, expected)
	matchedTarget := matchedTargetValues(rule.Comparator, trace.FinalValue, expected)

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
		"expected_values": expected,
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
		for _, word := range (wordlist.Loader{}).Words(rule.Edges.Wordlist) {
			expected = append(expected, word)
		}
	}
	return expected
}

func matchedExpectedValues(comparator string, value any, expected []any) []any {
	matches := make([]any, 0)
	for _, item := range expected {
		if comparisonMatchesExpected(comparator, value, item, expected) {
			matches = append(matches, item)
		}
	}
	if len(matches) == 0 && comparator == "@check" && stringify(value) != "" {
		return []any{value}
	}
	return matches
}

func matchedTargetValues(comparator string, value any, expected []any) []any {
	matches := make([]any, 0)
	for _, item := range toAnySlice(value) {
		if comparisonSatisfied(comparator, item, expected) {
			matches = append(matches, item)
		}
	}
	return matches
}

func comparisonMatchesExpected(comparator string, value any, expected any, allExpected []any) bool {
	switch comparator {
	case "@equal":
		return stringify(value) == stringify(expected)
	case "@contains", "@search":
		return strings.Contains(stringify(value), stringify(expected))
	case "@startsWith":
		return strings.HasPrefix(stringify(value), stringify(expected))
	case "@endsWith":
		return strings.HasSuffix(stringify(value), stringify(expected))
	case "@greaterThan", "@lessThan", "@greaterThanOrEqual", "@lessThanOrEqual":
		return len(allExpected) > 0 && stringify(expected) == stringify(allExpected[0]) && comparisonSatisfied(comparator, value, allExpected)
	case "@inRange":
		return len(allExpected) >= 2 && (stringify(expected) == stringify(allExpected[0]) || stringify(expected) == stringify(allExpected[1])) && comparisonSatisfied(comparator, value, allExpected)
	case "@match", "@regExp", "@checkRegExp":
		matched, _ := regexp.MatchString(stringify(expected), stringify(value))
		return matched
	case "@mirror":
		return containsString(toAnySlice(value), stringify(expected))
	case "@similar":
		return similarity(stringify(value), stringify(expected)) >= 0.8
	case "@check":
		return stringify(value) != ""
	default:
		return false
	}
}

func comparisonSatisfied(comparator string, value any, expected []any) bool {
	switch comparator {
	case "@check":
		return stringify(value) != ""
	case "@equal":
		return containsString(expected, stringify(value))
	case "@contains", "@search":
		text := stringify(value)
		for _, item := range expected {
			if strings.Contains(text, stringify(item)) {
				return true
			}
		}
	case "@startsWith":
		text := stringify(value)
		for _, item := range expected {
			if strings.HasPrefix(text, stringify(item)) {
				return true
			}
		}
	case "@endsWith":
		text := stringify(value)
		for _, item := range expected {
			if strings.HasSuffix(text, stringify(item)) {
				return true
			}
		}
	case "@greaterThan":
		return toFloat(value) > firstReportFloat(expected)
	case "@lessThan":
		return toFloat(value) < firstReportFloat(expected)
	case "@greaterThanOrEqual":
		return toFloat(value) >= firstReportFloat(expected)
	case "@lessThanOrEqual":
		return toFloat(value) <= firstReportFloat(expected)
	case "@inRange":
		return len(expected) >= 2 && toFloat(value) >= toFloat(expected[0]) && toFloat(value) <= toFloat(expected[1])
	case "@match", "@regExp", "@checkRegExp":
		text := stringify(value)
		for _, item := range expected {
			matched, _ := regexp.MatchString(stringify(item), text)
			if matched {
				return true
			}
		}
	case "@mirror":
		if len(expected) == 0 {
			return false
		}
		return stringify(value) == stringify(expected[0])
	case "@similar":
		for _, item := range expected {
			if similarity(stringify(value), stringify(item)) >= 0.8 {
				return true
			}
		}
	}
	return false
}

func contextualMatch(values []any, matches []any) []any {
	for index, value := range values {
		if !containsString(matches, stringify(value)) {
			continue
		}
		context := make([]any, 0, 3)
		if index > 0 {
			context = append(context, "...")
		}
		context = append(context, value)
		if index < len(values)-1 {
			context = append(context, "...")
		}
		return context
	}
	return nil
}

func containsString(values []any, needle string) bool {
	for _, value := range values {
		if stringify(value) == needle {
			return true
		}
	}
	return false
}

func firstReportFloat(values []any) float64 {
	if len(values) == 0 {
		return 0
	}
	return toFloat(values[0])
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

func similarity(a string, b string) float64 {
	if a == b {
		return 1
	}
	if a == "" || b == "" {
		return 0
	}
	distance := levenshtein(a, b)
	maxLen := math.Max(float64(len(a)), float64(len(b)))
	return 1 - float64(distance)/maxLen
}

func levenshtein(a string, b string) int {
	previous := make([]int, len(b)+1)
	for j := range previous {
		previous[j] = j
	}
	for i := 1; i <= len(a); i++ {
		current := make([]int, len(b)+1)
		current[0] = i
		for j := 1; j <= len(b); j++ {
			cost := 0
			if a[i-1] != b[j-1] {
				cost = 1
			}
			current[j] = min(previous[j]+1, current[j-1]+1, previous[j-1]+cost)
		}
		previous = current
	}
	return previous[len(b)]
}
