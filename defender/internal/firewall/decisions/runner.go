package decisions

import (
	"encoding/json"
	"fmt"
	"net/http"
	"strconv"
	"strings"

	"defly-defender/ent"
	entdecision "defly-defender/ent/decision"
	"defly-defender/internal/firewall/state"
)

type Transaction interface {
	ResultState() *state.Result
	ScoreValue() float64
	LevelValue() int
	RequestObject() *http.Request
	ResponseObject() *http.Response
	RawRequest() []byte
	RequestBodyBytes() []byte
	ResponseBodyBytes() []byte
	RequestContentType() string
	ResponseContentType() string
}

type Decision interface {
	DirectionValue() entdecision.Direction
	ConditionValue() entdecision.Condition
	ActionValue() entdecision.Action
	ScoreValue() float64
	ConfigurationsValue() map[string]any
}

type Runner struct {
	Decisions      []Decision
	ViolationScore int
}

type entityDecision struct {
	decision *ent.Decision
}

func FromEntities(items []*ent.Decision) []Decision {
	result := make([]Decision, 0, len(items))
	for _, item := range items {
		if item != nil {
			result = append(result, entityDecision{decision: item})
		}
	}
	return result
}

func (r Runner) Run(tx Transaction, direction entdecision.Direction) {
	if tx == nil || stopped(tx.ResultState(), direction) {
		return
	}
	result := tx.ResultState()
	if result == nil || result.Deny || result.Cancel {
		return
	}
	for _, decision := range r.Decisions {
		if decision == nil || decision.DirectionValue() != direction {
			continue
		}
		score := decision.ScoreValue()
		if score == 0 && r.ViolationScore > 0 {
			score = float64(r.ViolationScore)
		}
		if !matches(decision.ConditionValue(), tx.ScoreValue(), score) {
			continue
		}
		applyDecision(tx, decision)
		if result.Deny || result.Cancel || stopped(result, direction) {
			return
		}
	}
}

func (d entityDecision) DirectionValue() entdecision.Direction {
	return d.decision.Direction
}

func (d entityDecision) ConditionValue() entdecision.Condition {
	return d.decision.Condition
}

func (d entityDecision) ActionValue() entdecision.Action {
	return d.decision.Action
}

func (d entityDecision) ScoreValue() float64 {
	return d.decision.Score
}

func (d entityDecision) ConfigurationsValue() map[string]any {
	return d.decision.Configurations
}

func applyDecision(tx Transaction, decision Decision) {
	result := tx.ResultState()
	switch decision.ActionValue() {
	case entdecision.ActionAllow:
		result.Allow = true
		stopDirection(result, decision.DirectionValue())
	case entdecision.ActionDeny:
		applyDeny(result, decision)
	case entdecision.ActionRewriteHeaders:
		applyRewriteHeaders(result, decision.ConfigurationsValue())
	case entdecision.ActionRewriteBody:
		applyRewriteBody(tx, decision)
	case entdecision.ActionRedirect:
		result.RedirectURL = stringConfig(decision.ConfigurationsValue(), "url", "")
		stopAll(result)
	case entdecision.ActionCancel:
		result.Cancel = true
		stopAll(result)
	case entdecision.ActionRewrite:
		applyRewrite(result, decision.ConfigurationsValue())
	case entdecision.ActionEraseCookies:
		result.EraseCookies = true
	case entdecision.ActionForceNoCache:
		result.ForceNoCache = true
	case entdecision.ActionSave:
	}
}

func applyDeny(result *state.Result, decision Decision) {
	config := decision.ConfigurationsValue()
	result.Deny = true
	result.Status = int(numberConfig(config, "status", http.StatusForbidden))
	result.ContentType = denyContentType(config)
	body := stringConfig(config, "body", "")
	if body == "" {
		body = `{"message":"request denied"}`
	}
	result.Body = []byte(body)
	stopDirection(result, decision.DirectionValue())
}

func applyRewriteHeaders(result *state.Result, config map[string]any) {
	if stringConfig(config, "directive", "set") == "unset" {
		result.UnsetHeaders = append(result.UnsetHeaders, executionKeys(config, "unset")...)
		return
	}
	if result.RewriteHeaders == nil {
		result.RewriteHeaders = http.Header{}
	}
	for key, value := range executionKeyValueMap(config, "set") {
		result.RewriteHeaders.Set(key, value)
	}
}

func applyRewriteBody(tx Transaction, decision Decision) {
	config := decision.ConfigurationsValue()
	body := []byte(stringConfig(config, "body", ""))
	if len(body) == 0 {
		body = []byte(stringConfig(config, "value", ""))
	}
	if len(body) == 0 {
		return
	}
	result := tx.ResultState()
	result.BodyRewrite = body
	result.BodyRewritten = true
}

func applyRewrite(result *state.Result, config map[string]any) {
	if stringConfig(config, "type", "path") == "query" {
		queryConfig := mapConfig(config, "query")
		if stringConfig(queryConfig, "directive", "set") == "unset" {
			result.UnsetQuery = append(result.UnsetQuery, executionKeys(queryConfig, "unset")...)
			return
		}
		if result.RewriteQuery == nil {
			result.RewriteQuery = make(map[string]string)
		}
		for key, value := range executionKeyValueMap(queryConfig, "set") {
			result.RewriteQuery[key] = value
		}
		return
	}
	result.RewritePath = stringConfig(config, "path", "")
}

func stopped(result *state.Result, direction entdecision.Direction) bool {
	if result == nil {
		return true
	}
	switch direction {
	case entdecision.DirectionRequest:
		return result.StopRequestDecisions
	case entdecision.DirectionResponse:
		return result.StopResponseDecisions
	default:
		return false
	}
}

func stopDirection(result *state.Result, direction entdecision.Direction) {
	switch direction {
	case entdecision.DirectionRequest:
		result.StopRequestDecisions = true
	case entdecision.DirectionResponse:
		result.StopResponseDecisions = true
	}
}

func stopAll(result *state.Result) {
	result.StopRequestDecisions = true
	result.StopResponseDecisions = true
}

func matches(condition entdecision.Condition, actual float64, expected float64) bool {
	switch condition {
	case entdecision.ConditionLessThanOrEqual:
		return actual <= expected
	case entdecision.ConditionLessThan:
		return actual < expected
	case entdecision.ConditionEqual:
		return actual == expected
	case entdecision.ConditionGreaterThanOrEqual:
		return actual >= expected
	case entdecision.ConditionGreaterThan:
		return actual > expected
	default:
		return false
	}
}

func stringConfig(config map[string]any, key string, fallback string) string {
	if config == nil {
		return fallback
	}
	if value, ok := config[key]; ok {
		return stringify(value)
	}
	return fallback
}

func numberConfig(config map[string]any, key string, fallback float64) float64 {
	if config == nil {
		return fallback
	}
	if value, ok := config[key]; ok {
		return toFloat(value)
	}
	return fallback
}

func mapConfig(config map[string]any, key string) map[string]any {
	if config == nil {
		return nil
	}
	if value, ok := config[key].(map[string]any); ok {
		return value
	}
	return nil
}

func executionKeyValueMap(config map[string]any, directive string) map[string]string {
	return keyValueMapFromItems(executionItems(config, directive))
}

func executionKeys(config map[string]any, directive string) []string {
	return keysFromItems(executionItems(config, directive))
}

func executionItems(config map[string]any, directive string) []map[string]any {
	if config == nil {
		return nil
	}
	raw, ok := config["execution"]
	if !ok {
		return nil
	}
	if mapped, ok := raw.(map[string]any); ok {
		if selected, ok := mapped[directive]; ok {
			return itemsFromAny(selected)
		}
	}
	return itemsFromAny(raw)
}

func itemsFromAny(raw any) []map[string]any {
	switch typed := raw.(type) {
	case []any:
		result := make([]map[string]any, 0, len(typed))
		for _, item := range typed {
			if mapped, ok := item.(map[string]any); ok {
				result = append(result, mapped)
			}
		}
		return result
	case []map[string]any:
		return typed
	case map[string]any:
		result := make([]map[string]any, 0, len(typed))
		for key, value := range typed {
			result = append(result, map[string]any{
				"key":   key,
				"value": value,
			})
		}
		return result
	default:
		return nil
	}
}

func keyValueMapFromItems(items []map[string]any) map[string]string {
	result := make(map[string]string, len(items))
	for _, item := range items {
		result[stringify(item["key"])] = stringify(item["value"])
	}
	return result
}

func keysFromItems(items []map[string]any) []string {
	result := make([]string, 0, len(items))
	for _, item := range items {
		result = append(result, stringify(item["key"]))
	}
	return result
}

func denyContentType(config map[string]any) string {
	switch stringConfig(config, "content_type", "json") {
	case "html":
		return "text/html; charset=utf-8"
	default:
		return "application/json"
	}
}

func stringify(value any) string {
	switch typed := value.(type) {
	case nil:
		return ""
	case string:
		return typed
	case []byte:
		return string(typed)
	case fmt.Stringer:
		return typed.String()
	default:
		return fmt.Sprint(typed)
	}
}

func toFloat(value any) float64 {
	switch typed := value.(type) {
	case int:
		return float64(typed)
	case int64:
		return float64(typed)
	case uint64:
		return float64(typed)
	case float32:
		return float64(typed)
	case float64:
		return typed
	case json.Number:
		number, _ := typed.Float64()
		return number
	default:
		number, _ := strconv.ParseFloat(strings.TrimSpace(stringify(value)), 64)
		return number
	}
}
