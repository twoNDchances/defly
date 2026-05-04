package request

import decisionaction "defly-defender/internal/waf/decisions/action"

type Rewrite struct {
	Type         string
	Path         string
	QuerySet     map[string]string
	QueryUnset   []string
	QueryUnsetOn bool
}

func NewRewrite(config map[string]any) Rewrite {
	action := Rewrite{
		Type: stringConfig(config, "type", "path"),
		Path: stringConfig(config, "path", ""),
	}
	if action.Type == "query" {
		queryConfig := mapConfig(config, "query")
		if stringConfig(queryConfig, "directive", "set") == "unset" {
			action.QueryUnsetOn = true
			action.QueryUnset = executionKeys(queryConfig, "unset")
			return action
		}
		action.QuerySet = executionKeyValueMap(queryConfig, "set")
	}
	return action
}

func (a Rewrite) Apply(result *decisionaction.Result) {
	if a.Type == "query" {
		a.applyQuery(result)
		return
	}
	result.RewritePath = a.Path
}

func (a Rewrite) applyQuery(result *decisionaction.Result) {
	if a.QueryUnsetOn {
		result.UnsetQuery = append(result.UnsetQuery, a.QueryUnset...)
		return
	}
	if result.RewriteQuery == nil {
		result.RewriteQuery = make(map[string]string)
	}
	for key, value := range a.QuerySet {
		result.RewriteQuery[key] = value
	}
}
