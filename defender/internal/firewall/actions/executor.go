package actions

import (
	"encoding/json"
	"fmt"
	"log"
	"net/http"
	"strconv"
	"strings"

	"defly-defender/ent"
	entaction "defly-defender/ent/action"
)

type Executor struct {
	Severity          map[string]int
	Client            *http.Client
	ReportDatabaseDSN string
	ReportDefenderID  string
	Rule              *ent.Rule
}

func (e Executor) Execute(tx Transaction, rule *ent.Rule, items []*ent.Action) {
	e.Rule = rule
	for _, item := range items {
		if item == nil || tx == nil || tx.IsAllowed() || tx.IsDenied() {
			return
		}
		runtimeAction, err := e.build(item)
		if err != nil {
			log.Println("firewall action validation failed:", err)
		}
		if runtimeAction == nil {
			continue
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
		runtimeAction = Request{Send: func() { sendRequest(cfg, e.Client) }}
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

func applyBehavior(current float64, value float64, behavior string) float64 {
	switch behavior {
	case "addition", "add", "+":
		return current + value
	case "subtraction", "subtract", "-":
		return current - value
	case "multiplication", "*":
		return current * value
	case "division", "/":
		if value == 0 {
			return current
		}
		return current / value
	case "increase":
		return current + value
	case "decrease":
		return current - value
	default:
		return value
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

func configItems(config map[string]any, key string) []map[string]any {
	raw, ok := config[key]
	if !ok {
		return nil
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

func keyValueMap(config map[string]any, key string) map[string]string {
	return keyValueMapFromItems(configItems(config, key))
}

func keyValueMapFromItems(items []map[string]any) map[string]string {
	result := make(map[string]string, len(items))
	for _, item := range items {
		result[stringify(item["key"])] = stringify(item["value"])
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
		return "storage/logs/firewall.log"
	}
	return ""
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

func castDatatype(value any, datatype string) any {
	switch datatype {
	case "array":
		return toAnySlice(value)
	case "number":
		return toFloat(value)
	case "string":
		return stringify(value)
	default:
		return value
	}
}

func toAnySlice(value any) []any {
	switch typed := value.(type) {
	case nil:
		return []any{nil}
	case []any:
		return typed
	case []string:
		items := make([]any, 0, len(typed))
		for _, item := range typed {
			items = append(items, item)
		}
		return items
	default:
		return []any{typed}
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
