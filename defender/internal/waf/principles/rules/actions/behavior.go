package actions

import (
	"encoding/json"
	"fmt"
	"strconv"
	"strings"
)

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

func floatString(value float64) string {
	return strconv.FormatFloat(value, 'f', -1, 64)
}
