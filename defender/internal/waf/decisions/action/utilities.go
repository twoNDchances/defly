package action

import (
	"bytes"
	"encoding/json"
	"fmt"
	"io"
	"mime"
	"mime/multipart"
	"net/url"
	"strconv"
	"strings"
)

type filePart struct {
	Filename string
	Content  []byte
	Size     int64
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

func denyContentType(config map[string]any) string {
	switch stringConfig(config, "content_type", "json") {
	case "html":
		return "text/html; charset=utf-8"
	default:
		return "application/json"
	}
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

func parseBody(body []byte, contentType string) (map[string]any, map[string][]filePart) {
	fields := make(map[string]any)
	files := make(map[string][]filePart)
	if len(body) == 0 {
		return fields, files
	}

	mediaType, params, _ := mime.ParseMediaType(contentType)
	switch mediaType {
	case "application/json":
		var decoded any
		if err := json.Unmarshal(body, &decoded); err == nil {
			if mapped, ok := decoded.(map[string]any); ok {
				return mapped, files
			}
			fields["body"] = decoded
		}
	case "application/x-www-form-urlencoded":
		values, err := url.ParseQuery(string(body))
		if err == nil {
			for key, value := range values {
				if len(value) == 1 {
					fields[key] = value[0]
				} else {
					fields[key] = value
				}
			}
		}
	case "multipart/form-data":
		reader := multipart.NewReader(bytes.NewReader(body), params["boundary"])
		for {
			part, err := reader.NextPart()
			if err == io.EOF {
				break
			}
			if err != nil {
				break
			}
			content, _ := io.ReadAll(part)
			name := part.FormName()
			if name == "" {
				continue
			}
			if part.FileName() == "" {
				fields[name] = string(content)
				continue
			}
			files[name] = append(files[name], filePart{
				Filename: part.FileName(),
				Content:  content,
				Size:     int64(len(content)),
			})
		}
	default:
		fields["body"] = string(body)
	}
	return fields, files
}

func toStrings(value any) []string {
	items := toAnySlice(value)
	result := make([]string, 0, len(items))
	for _, item := range items {
		result = append(result, stringify(item))
	}
	return result
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
