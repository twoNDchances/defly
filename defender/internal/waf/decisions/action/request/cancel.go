package request

import decisionaction "defly-defender/internal/waf/decisions/action"

type Cancel struct{}

func (Cancel) Apply(result *decisionaction.Result) {
	result.Cancel = true
	decisionaction.Stopper{}.All(result)
}
