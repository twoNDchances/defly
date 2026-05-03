package response

import decisionaction "defly-defender/internal/waf/decisions/action"

type EraseCookies struct{}

func (EraseCookies) Apply(result *decisionaction.Result) {
	result.EraseCookies = true
}
