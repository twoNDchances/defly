package request

import decisionaction "defly-defender/internal/waf/decisions/action"

type Redirect struct{}

func (Redirect) Apply(result *decisionaction.Result, config map[string]any) {
	result.RedirectURL = stringConfig(config, "url", "")
	decisionaction.Stopper{}.All(result)
}
