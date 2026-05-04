package action

import (
	"encoding/json"
	"mime"
	"net/url"
	"strings"

	entdecision "defly-defender/ent/decision"
)

type RewriteBody struct {
	Direction entdecision.Direction
	Directive string
	Body      []byte
	Set       map[string]string
	Unset     []string
}

func NewRewriteBody(decision Decision) RewriteBody {
	config := decision.ConfigurationsValue()
	return RewriteBody{
		Direction: decision.DirectionValue(),
		Directive: stringConfig(config, "directive", "set"),
		Body:      []byte(stringConfig(config, "body", "")),
		Set:       executionKeyValueMap(config, "set"),
		Unset:     executionKeys(config, "unset"),
	}
}

func (a RewriteBody) Apply(tx Transaction) {
	body, contentType := bodyContext(tx, a.Direction)
	if rewritten, ok := a.rewrite(body, contentType); ok {
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

func (a RewriteBody) rewrite(body []byte, contentType string) ([]byte, bool) {
	if a.Directive == "unset" {
		if len(a.Unset) == 0 {
			return body, false
		}
		return unsetBodyFields(body, contentType, a.Unset), true
	}

	if len(a.Set) == 0 {
		return a.Body, len(a.Body) > 0
	}
	if value, ok := directBodyRewrite(a.Set); ok {
		return []byte(value), true
	}
	return setBodyFields(body, contentType, a.Set), true
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
