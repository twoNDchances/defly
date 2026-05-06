package waf

import (
	"defly-defender/ent"
	"defly-defender/internal/globals"
	principleruntime "defly-defender/internal/waf/principles"
)

type Phases struct {
	core Core
}

func (p Phases) Run(tx *Transaction, phase int) {
	principleruntime.Runner{
		Principles: globals.Principles,
		Rules:      phaseRules{core: p.core, tx: tx},
		Actions:    phaseActions{core: p.core, tx: tx},
	}.Run(tx, phase)
}

type phaseRules struct {
	core Core
	tx   *Transaction
}

func (p phaseRules) Match(rule *ent.Rule, phase int) bool {
	return p.core.Rules().Match(p.tx, rule, phase)
}

type phaseActions struct {
	core Core
	tx   *Transaction
}

func (p phaseActions) Execute(rule *ent.Rule, actions []*ent.Action) {
	p.core.Actions().Execute(p.tx, rule, actions)
}
