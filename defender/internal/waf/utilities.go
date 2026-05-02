package waf

import (
	"bytes"
	"crypto/md5"
	"crypto/sha1"
	"crypto/sha256"
	"crypto/sha512"
	"encoding/hex"
	"encoding/json"
	"fmt"
	"io"
	"math"
	"mime"
	"mime/multipart"
	"net/http"
	"net/url"
	"path/filepath"
	"strconv"
	"strings"
)

type Utilities struct{}

type filePart struct {
	Filename string
	Content  []byte
	Size     int64
}

func (u Utilities) headersString(headers http.Header) string {
	var builder strings.Builder
	_ = headers.Write(&builder)
	return builder.String()
}

func (u Utilities) headerKeys(headers http.Header) []string {
	keys := make([]string, 0, len(headers))
	for key := range headers {
		keys = append(keys, key)
	}
	return keys
}

func (u Utilities) headerValues(headers http.Header) []string {
	values := make([]string, 0)
	for _, headerValues := range headers {
		values = append(values, headerValues...)
	}
	return values
}

func (u Utilities) queryKeys(query url.Values) []string {
	keys := make([]string, 0, len(query))
	for key := range query {
		keys = append(keys, key)
	}
	return keys
}

func (u Utilities) queryValues(query url.Values) []string {
	values := make([]string, 0)
	for _, queryValues := range query {
		values = append(values, queryValues...)
	}
	return values
}

func (u Utilities) urlPort(request *http.Request) float64 {
	port := request.URL.Port()
	if port == "" {
		if request.TLS != nil {
			return 443
		}
		return 80
	}
	return u.toFloat(port)
}

func (u Utilities) bodyValue(body map[string]any, key string) any {
	if key == "" {
		return body
	}
	return u.dotted(body, key)
}

func (u Utilities) fileValue(files map[string][]filePart, key string) any {
	parts, ok := files[key]
	if !ok || len(parts) == 0 {
		return nil
	}
	values := make([]string, 0, len(parts))
	for _, part := range parts {
		values = append(values, part.Filename)
	}
	if len(values) == 1 {
		return values[0]
	}
	return values
}

func (u Utilities) parseBody(body []byte, contentType string) (map[string]any, map[string][]filePart) {
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

func (u Utilities) mapKeys(values map[string]any) []string {
	keys := make([]string, 0, len(values))
	for key := range values {
		keys = append(keys, key)
	}
	return keys
}

func (u Utilities) mapValues(values map[string]any) []string {
	items := make([]string, 0, len(values))
	for _, value := range values {
		items = append(items, u.stringify(value))
	}
	return items
}

func (u Utilities) mapKeysFiles(values map[string][]filePart) []string {
	keys := make([]string, 0, len(values))
	for key := range values {
		keys = append(keys, key)
	}
	return keys
}

func (u Utilities) fileNames(values map[string][]filePart) []string {
	names := make([]string, 0)
	for _, parts := range values {
		for _, part := range parts {
			names = append(names, part.Filename)
		}
	}
	return names
}

func (u Utilities) fileExtensions(values map[string][]filePart) []string {
	extensions := make([]string, 0)
	for _, name := range u.fileNames(values) {
		extensions = append(extensions, strings.TrimPrefix(filepath.Ext(name), "."))
	}
	return extensions
}

func (u Utilities) fileContents(values map[string][]filePart) []string {
	contents := make([]string, 0)
	for _, parts := range values {
		for _, part := range parts {
			contents = append(contents, string(part.Content))
		}
	}
	return contents
}

func (u Utilities) fileLength(values map[string][]filePart) float64 {
	var length int64
	for _, parts := range values {
		for _, part := range parts {
			length += part.Size
		}
	}
	return float64(length)
}

func (u Utilities) dotted(value any, key string) any {
	current := value
	for _, part := range strings.Split(key, ".") {
		switch typed := current.(type) {
		case map[string]any:
			current = typed[part]
		case []any:
			index, err := strconv.Atoi(part)
			if err != nil || index < 0 || index >= len(typed) {
				return nil
			}
			current = typed[index]
		default:
			return nil
		}
	}
	return current
}

func (u Utilities) castDatatype(value any, datatype string) any {
	switch datatype {
	case "array":
		return u.toAnySlice(value)
	case "number":
		return u.toFloat(value)
	case "string":
		return u.stringify(value)
	default:
		return value
	}
}

func (u Utilities) toAnySlice(value any) []any {
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

func (u Utilities) toStrings(value any) []string {
	items := u.toAnySlice(value)
	result := make([]string, 0, len(items))
	for _, item := range items {
		result = append(result, u.stringify(item))
	}
	return result
}

func (u Utilities) stringify(value any) string {
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

func (u Utilities) toFloat(value any) float64 {
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
		number, _ := strconv.ParseFloat(strings.TrimSpace(u.stringify(value)), 64)
		return number
	}
}

func (u Utilities) firstFloat(values []any) float64 {
	return u.toFloat(u.expectedValue(values))
}

func (u Utilities) expectedValue(values []any) any {
	if len(values) == 0 {
		return nil
	}
	return values[0]
}

func (u Utilities) stringConfig(config map[string]any, key string, fallback string) string {
	if config == nil {
		return fallback
	}
	if value, ok := config[key]; ok {
		return u.stringify(value)
	}
	return fallback
}

func (u Utilities) numberConfig(config map[string]any, key string, fallback float64) float64 {
	if config == nil {
		return fallback
	}
	if value, ok := config[key]; ok {
		return u.toFloat(value)
	}
	return fallback
}

func (u Utilities) configItems(config map[string]any, key string) []map[string]any {
	raw, ok := config[key]
	if !ok {
		return nil
	}
	return u.itemsFromAny(raw)
}

func (u Utilities) itemsFromAny(raw any) []map[string]any {
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

func (u Utilities) keyValueMap(config map[string]any, key string) map[string]string {
	return u.keyValueMapFromItems(u.configItems(config, key))
}

func (u Utilities) keyValueMapFromItems(items []map[string]any) map[string]string {
	result := make(map[string]string, len(items))
	for _, item := range items {
		result[u.stringify(item["key"])] = u.stringify(item["value"])
	}
	return result
}

func (u Utilities) keys(config map[string]any, key string) []string {
	return u.keysFromItems(u.configItems(config, key))
}

func (u Utilities) keysFromItems(items []map[string]any) []string {
	result := make([]string, 0, len(items))
	for _, item := range items {
		result = append(result, u.stringify(item["key"]))
	}
	return result
}

func (u Utilities) executionItems(config map[string]any, directive string) []map[string]any {
	if config == nil {
		return nil
	}
	raw, ok := config["execution"]
	if !ok {
		return nil
	}
	if mapped, ok := raw.(map[string]any); ok {
		if selected, ok := mapped[directive]; ok {
			return u.itemsFromAny(selected)
		}
	}
	return u.itemsFromAny(raw)
}

func (u Utilities) executionKeyValueMap(config map[string]any, directive string) map[string]string {
	return u.keyValueMapFromItems(u.executionItems(config, directive))
}

func (u Utilities) executionKeys(config map[string]any, directive string) []string {
	return u.keysFromItems(u.executionItems(config, directive))
}

func (u Utilities) applyHeaderDirective(result *DecisionResult, config map[string]any) {
	if result.RewriteHeaders == nil {
		result.RewriteHeaders = http.Header{}
	}
	if u.stringConfig(config, "directive", "set") == "unset" {
		result.UnsetHeaders = append(result.UnsetHeaders, u.executionKeys(config, "unset")...)
		return
	}
	for key, value := range u.executionKeyValueMap(config, "set") {
		result.RewriteHeaders.Set(key, value)
	}
}

func (u Utilities) bodyRewriteValue(config map[string]any) string {
	for _, item := range u.executionItems(config, "set") {
		if u.stringify(item["key"]) == "" || u.stringify(item["key"]) == "body" {
			return u.stringify(item["value"])
		}
	}
	return u.stringConfig(config, "body", "")
}

func (u Utilities) rewriteBody(body []byte, contentType string, config map[string]any) ([]byte, bool) {
	directive := u.stringConfig(config, "directive", "set")
	if directive == "unset" {
		keys := u.executionKeys(config, "unset")
		if len(keys) == 0 {
			return body, false
		}
		return u.unsetBodyFields(body, contentType, keys), true
	}

	values := u.executionKeyValueMap(config, "set")
	if len(values) == 0 {
		value := u.stringConfig(config, "body", "")
		return []byte(value), value != ""
	}
	if value, ok := u.directBodyRewrite(values); ok {
		return []byte(value), true
	}
	return u.setBodyFields(body, contentType, values), true
}

func (u Utilities) directBodyRewrite(values map[string]string) (string, bool) {
	if len(values) != 1 {
		return "", false
	}
	for key, value := range values {
		if key == "" || key == "body" {
			return value, true
		}
	}
	return "", false
}

func (u Utilities) setBodyFields(body []byte, contentType string, values map[string]string) []byte {
	fields, _ := u.parseBody(body, contentType)
	for key, value := range values {
		u.setDotted(fields, key, value)
	}
	rewritten := u.encodeBodyFields(fields, contentType)
	if rewritten == nil {
		return body
	}
	return rewritten
}

func (u Utilities) unsetBodyFields(body []byte, contentType string, keys []string) []byte {
	fields, _ := u.parseBody(body, contentType)
	for _, key := range keys {
		u.unsetDotted(fields, key)
	}
	rewritten := u.encodeBodyFields(fields, contentType)
	if rewritten == nil {
		return body
	}
	return rewritten
}

func (u Utilities) setDotted(values map[string]any, key string, value any) {
	if key == "" {
		values["body"] = value
		return
	}
	parts := strings.Split(key, ".")
	current := values
	for _, part := range parts[:len(parts)-1] {
		next, ok := current[part].(map[string]any)
		if !ok {
			next = make(map[string]any)
			current[part] = next
		}
		current = next
	}
	current[parts[len(parts)-1]] = value
}

func (u Utilities) unsetDotted(values map[string]any, key string) {
	if key == "" {
		delete(values, "body")
		return
	}
	parts := strings.Split(key, ".")
	current := values
	for _, part := range parts[:len(parts)-1] {
		next, ok := current[part].(map[string]any)
		if !ok {
			return
		}
		current = next
	}
	delete(current, parts[len(parts)-1])
}

func (u Utilities) encodeBodyFields(fields map[string]any, contentType string) []byte {
	mediaType, _, _ := mime.ParseMediaType(contentType)
	switch mediaType {
	case "application/json":
		body, err := json.Marshal(fields)
		if err == nil {
			return body
		}
	case "application/x-www-form-urlencoded":
		values := url.Values{}
		for key, value := range fields {
			for _, item := range u.toStrings(value) {
				values.Add(key, item)
			}
		}
		return []byte(values.Encode())
	case "multipart/form-data":
		if value, ok := fields["body"]; ok && len(fields) == 1 {
			return []byte(u.stringify(value))
		}
		return nil
	default:
		if value, ok := fields["body"]; ok && len(fields) == 1 {
			return []byte(u.stringify(value))
		}
		body, err := json.Marshal(fields)
		if err == nil {
			return body
		}
	}
	return nil
}

func (u Utilities) denyContentType(config map[string]any) string {
	switch u.stringConfig(config, "content_type", "json") {
	case "html":
		return "text/html; charset=utf-8"
	default:
		return "application/json"
	}
}

func (u Utilities) applyBehavior(current float64, value float64, behavior string) float64 {
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

func (u Utilities) hashString(value string, method string) string {
	switch strings.ToLower(method) {
	case "md5":
		sum := md5.Sum([]byte(value))
		return hex.EncodeToString(sum[:])
	case "sha1":
		sum := sha1.Sum([]byte(value))
		return hex.EncodeToString(sum[:])
	case "sha224":
		sum := sha256.Sum224([]byte(value))
		return hex.EncodeToString(sum[:])
	case "sha512":
		sum := sha512.Sum512([]byte(value))
		return hex.EncodeToString(sum[:])
	default:
		sum := sha256.Sum256([]byte(value))
		return hex.EncodeToString(sum[:])
	}
}

func (u Utilities) similarity(a string, b string) float64 {
	if a == b {
		return 1
	}
	if a == "" || b == "" {
		return 0
	}
	distance := u.levenshtein(a, b)
	maxLen := math.Max(float64(len(a)), float64(len(b)))
	return 1 - float64(distance)/maxLen
}

func (u Utilities) levenshtein(a string, b string) int {
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

func (u Utilities) floatString(value float64) string {
	return strconv.FormatFloat(value, 'f', -1, 64)
}
