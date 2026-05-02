package waf

import (
	"defly-defender/ent"
	"defly-defender/internal/globals"
)

type Phases struct {
	core Core
}

func (p Phases) Run(tx *Transaction, phase int) {
	if tx == nil || tx.Result.Allow || tx.Result.Deny {
		return
	}

	maxLevel := tx.Level
	if maxLevel < 1 {
		maxLevel = 1
	}
	for level := 1; level <= maxLevel; level++ {
		principles := p.principlesForPhaseAndLevel(phase, level)
		for _, principle := range principles {
			if tx.Result.Allow || tx.Result.Deny || tx.Level < level {
				return
			}
			p.runPrinciple(tx, principle, phase)
		}
		maxLevel = tx.Level
		if maxLevel < level {
			return
		}
	}
}

func (p Phases) principlesForPhaseAndLevel(phase int, level int) []*ent.Principle {
	principles := make([]*ent.Principle, 0)
	for _, principle := range globals.Principles {
		if principle == nil || principle.Phase != phase || int(principle.Level) != level {
			continue
		}
		principles = append(principles, principle)
	}
	return principles
}

func (p Phases) runPrinciple(tx *Transaction, principle *ent.Principle, phase int) {
	rules := principle.Edges.Rules
	if len(rules) == 0 {
		return
	}
	actionGroups := make([][]*ent.Action, 0, len(rules))
	for _, rule := range rules {
		if tx.Result.Allow || tx.Result.Deny {
			return
		}
		if rule == nil || rule.Phase != phase {
			continue
		}
		matched := p.core.Rules().Match(tx, rule, phase)
		if rule.IsInversed {
			matched = !matched
		}
		if !matched {
			return
		}
		actionGroups = append(actionGroups, rule.Edges.Actions)
	}
	for _, actions := range actionGroups {
		p.core.Actions().Execute(tx, actions)
	}
}
