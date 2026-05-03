package engines

import (
	"encoding/json"
	"fmt"
	"strconv"
	"strings"
)

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

func toStrings(value any) []string {
	items := toAnySlice(value)
	result := make([]string, 0, len(items))
	for _, item := range items {
		result = append(result, stringify(item))
	}
	return result
}

func joinStrings(values []string, separator string) string {
	return strings.Join(values, separator)
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
