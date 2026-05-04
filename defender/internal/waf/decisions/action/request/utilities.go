package request

import (
	"fmt"
)

func stringConfig(config map[string]any, key string, fallback string) string {
	if config == nil {
		return fallback
	}
	if value, ok := config[key]; ok {
		return stringify(value)
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
