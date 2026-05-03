package action

import (
	"encoding/json"
	"mime"
	"net/url"
	"strings"

	entdecision "defly-defender/ent/decision"
)

type RewriteBody struct{}

func (RewriteBody) Apply(tx Transaction, decision Decision) {
	body, contentType := bodyContext(tx, decision.DirectionValue())
	if rewritten, ok := rewriteBody(body, contentType, decision.ConfigurationsValue()); ok {
		result := tx.ResultState()
		result.BodyRewrite = rewritten
		result.BodyRewritten = true
	}
}

func bodyContext(tx Transaction, direction entdecision.Direction) ([]byte, string) {
	if direction == entdecision.DirectionResponse && tx.ResponseObject() != nil {
		return tx.ResponseBodyBytes(), tx.ResponseContentType()
	}
	if tx.RequestObject() != nil {
		return tx.RequestBodyBytes(), tx.RequestContentType()
	}
	return nil, ""
}

func rewriteBody(body []byte, contentType string, config map[string]any) ([]byte, bool) {
	directive := stringConfig(config, "directive", "set")
	if directive == "unset" {
		keys := executionKeys(config, "unset")
		if len(keys) == 0 {
			return body, false
		}
		return unsetBodyFields(body, contentType, keys), true
	}

	values := executionKeyValueMap(config, "set")
	if len(values) == 0 {
		value := stringConfig(config, "body", "")
		return []byte(value), value != ""
	}
	if value, ok := directBodyRewrite(values); ok {
		return []byte(value), true
	}
	return setBodyFields(body, contentType, values), true
}

func directBodyRewrite(values map[string]string) (string, bool) {
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

func setBodyFields(body []byte, contentType string, values map[string]string) []byte {
	fields, _ := parseBody(body, contentType)
	for key, value := range values {
		setDotted(fields, key, value)
	}
	rewritten := encodeBodyFields(fields, contentType)
	if rewritten == nil {
		return body
	}
	return rewritten
}

func unsetBodyFields(body []byte, contentType string, keys []string) []byte {
	fields, _ := parseBody(body, contentType)
	for _, key := range keys {
		unsetDotted(fields, key)
	}
	rewritten := encodeBodyFields(fields, contentType)
	if rewritten == nil {
		return body
	}
	return rewritten
}

func setDotted(values map[string]any, key string, value any) {
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

func unsetDotted(values map[string]any, key string) {
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

func encodeBodyFields(fields map[string]any, contentType string) []byte {
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
			for _, item := range toStrings(value) {
				values.Add(key, item)
			}
		}
		return []byte(values.Encode())
	default:
		if value, ok := fields["body"]; ok && len(fields) == 1 {
			return []byte(stringify(value))
		}
		body, err := json.Marshal(fields)
		if err == nil {
			return body
		}
	}
	return nil
}
