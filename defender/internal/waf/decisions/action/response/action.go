package response

import (
	entdecision "defly-defender/ent/decision"
	decisionaction "defly-defender/internal/waf/decisions/action"
)

type Executor struct{}

func (Executor) Apply(tx decisionaction.Transaction, decision decisionaction.Decision) {
	if tx == nil || decision == nil || tx.ResultState() == nil {
		return
	}
	switch decision.ActionValue() {
	case entdecision.ActionEraseCookies:
		EraseCookies{}.Apply(tx.ResultState())
	case entdecision.ActionForceNoCache:
		ForceNoCache{}.Apply(tx.ResultState())
	}
}
