package principles

import "defly-defender/ent"

type Transaction interface {
	IsAllowed() bool
	IsDenied() bool
	CurrentLevel() int
}

type RuleMatcher interface {
	Match(rule *ent.Rule, phase int) bool
}

type ActionExecutor interface {
	Execute(actions []*ent.Action)
}

type Runner struct {
	Principles []*ent.Principle
	Rules      RuleMatcher
	Actions    ActionExecutor
}

func (r Runner) Run(tx Transaction, phase int) {
	if tx == nil || tx.IsAllowed() || tx.IsDenied() {
		return
	}

	maxLevel := tx.CurrentLevel()
	if maxLevel < 1 {
		maxLevel = 1
	}
	for level := 1; level <= maxLevel; level++ {
		principles := r.principlesForPhaseAndLevel(phase, level)
		for _, principle := range principles {
			if tx.IsAllowed() || tx.IsDenied() || tx.CurrentLevel() < level {
				return
			}
			r.runPrinciple(tx, principle, phase)
		}
		maxLevel = tx.CurrentLevel()
		if maxLevel < level {
			return
		}
	}
}

func (r Runner) principlesForPhaseAndLevel(phase int, level int) []*ent.Principle {
	principles := make([]*ent.Principle, 0)
	for _, principle := range r.Principles {
		if principle == nil || principle.Phase != phase || int(principle.Level) != level {
			continue
		}
		principles = append(principles, principle)
	}
	return principles
}

func (r Runner) runPrinciple(tx Transaction, principle *ent.Principle, phase int) {
	rules := principle.Edges.Rules
	if len(rules) == 0 {
		return
	}
	actionGroups := make([][]*ent.Action, 0, len(rules))
	for _, rule := range rules {
		if tx.IsAllowed() || tx.IsDenied() {
			return
		}
		if rule == nil || rule.Phase != phase {
			continue
		}
		matched := r.Rules.Match(rule, phase)
		if rule.IsInversed {
			matched = !matched
		}
		if !matched {
			return
		}
		actionGroups = append(actionGroups, rule.Edges.Actions)
	}
	for _, actions := range actionGroups {
		r.Actions.Execute(actions)
	}
}
