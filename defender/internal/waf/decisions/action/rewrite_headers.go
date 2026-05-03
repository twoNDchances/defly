package action

import "net/http"

type RewriteHeaders struct{}

func (RewriteHeaders) Apply(result *Result, config map[string]any) {
	if result.RewriteHeaders == nil {
		result.RewriteHeaders = http.Header{}
	}
	if stringConfig(config, "directive", "set") == "unset" {
		result.UnsetHeaders = append(result.UnsetHeaders, executionKeys(config, "unset")...)
		return
	}
	for key, value := range executionKeyValueMap(config, "set") {
		result.RewriteHeaders.Set(key, value)
	}
}
