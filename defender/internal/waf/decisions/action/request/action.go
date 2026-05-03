package request

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
	case entdecision.ActionRedirect:
		Redirect{}.Apply(tx.ResultState(), decision.ConfigurationsValue())
	case entdecision.ActionCancel:
		Cancel{}.Apply(tx.ResultState())
	case entdecision.ActionRewrite:
		Rewrite{}.Apply(tx.ResultState(), decision.ConfigurationsValue())
	case entdecision.ActionSave:
		Save{}.Apply(tx, decision.DirectionValue(), decision.ConfigurationsValue())
	}
}
