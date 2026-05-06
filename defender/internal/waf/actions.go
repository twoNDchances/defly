package waf

import (
	"defly-defender/ent"
	ruleactions "defly-defender/internal/waf/principles/rules/actions"
)

type Actions struct {
	core Core
}

func (a Actions) Execute(tx *Transaction, rule *ent.Rule, actions []*ent.Action) {
	ruleactions.Executor{
		Severity:          a.core.Config.Severity,
		ReportDatabaseDSN: a.core.Config.ReportDatabaseDSN,
		ReportDefenderID:  a.core.Config.ReportDefenderID,
	}.Execute(tx, rule, actions)
}
