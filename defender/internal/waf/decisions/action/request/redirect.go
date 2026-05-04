package request

import decisionaction "defly-defender/internal/waf/decisions/action"

type Redirect struct {
	URL string
}

func NewRedirect(config map[string]any) Redirect {
	return Redirect{URL: stringConfig(config, "url", "")}
}

func (a Redirect) Apply(result *decisionaction.Result) {
	result.RedirectURL = a.URL
	decisionaction.Stopper{}.All(result)
}
