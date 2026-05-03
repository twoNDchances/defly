package response

import decisionaction "defly-defender/internal/waf/decisions/action"

type ForceNoCache struct{}

func (ForceNoCache) Apply(result *decisionaction.Result) {
	result.ForceNoCache = true
}
