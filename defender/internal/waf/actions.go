package waf

import (
	"defly-defender/ent"
	ruleactions "defly-defender/internal/waf/principles/rules/actions"
)

type Actions struct {
	core Core
}

func (a Actions) Execute(tx *Transaction, actions []*ent.Action) {
	ruleactions.Executor{Severity: a.core.Config.Severity}.Execute(tx, actions)
}
