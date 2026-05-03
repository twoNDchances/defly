package waf

import (
	"defly-defender/ent"
	ruleruntime "defly-defender/internal/waf/principles/rules"
	"defly-defender/internal/waf/wordlist"
)

type Rules struct {
	core Core
}

func (r Rules) Match(tx *Transaction, rule *ent.Rule, phase int) bool {
	return ruleruntime.Matcher{
		Targets:  ruleTargets{core: r.core, tx: tx},
		Engines:  ruleEngines{core: r.core},
		Wordlist: wordlist.Loader{},
	}.Match(rule, phase)
}

type ruleTargets struct {
	core Core
	tx   *Transaction
}

func (r ruleTargets) Extract(target *ent.Target, phase int) any {
	return r.core.Targets().Extract(r.tx, target, phase)
}

type ruleEngines struct {
	core Core
}

func (r ruleEngines) Transform(value any, target *ent.Target) any {
	return r.core.Engine().TransformTarget(value, target)
}
