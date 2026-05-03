package request

import decisionaction "defly-defender/internal/waf/decisions/action"

type Rewrite struct{}

func (r Rewrite) Apply(result *decisionaction.Result, config map[string]any) {
	if stringConfig(config, "type", "path") == "query" {
		r.applyQuery(result, mapConfig(config, "query"))
		return
	}
	result.RewritePath = stringConfig(config, "path", "")
}

func (Rewrite) applyQuery(result *decisionaction.Result, config map[string]any) {
	if stringConfig(config, "directive", "set") == "unset" {
		result.UnsetQuery = append(result.UnsetQuery, executionKeys(config, "unset")...)
		return
	}
	if result.RewriteQuery == nil {
		result.RewriteQuery = make(map[string]string)
	}
	for key, value := range executionKeyValueMap(config, "set") {
		result.RewriteQuery[key] = value
	}
}
