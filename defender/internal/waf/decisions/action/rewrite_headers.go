package action

import "net/http"

type RewriteHeaders struct {
	Set   http.Header
	Unset []string
}

func NewRewriteHeaders(config map[string]any) RewriteHeaders {
	if stringConfig(config, "directive", "set") == "unset" {
		return RewriteHeaders{Unset: executionKeys(config, "unset")}
	}
	headers := http.Header{}
	for key, value := range executionKeyValueMap(config, "set") {
		headers.Set(key, value)
	}
	return RewriteHeaders{Set: headers}
}

func (a RewriteHeaders) Apply(result *Result) {
	if result.RewriteHeaders == nil {
		result.RewriteHeaders = http.Header{}
	}
	for key, values := range a.Set {
		result.RewriteHeaders.Del(key)
		for _, value := range values {
			result.RewriteHeaders.Add(key, value)
		}
	}
	result.UnsetHeaders = append(result.UnsetHeaders, a.Unset...)
}
