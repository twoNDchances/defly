package waf

import (
	"defly-defender/ent"
	ruleengines "defly-defender/internal/waf/principles/rules/targets/engines"
)

type Engine struct {
	core Core
}

func (e Engine) TransformTarget(value any, target *ent.Target) any {
	return ruleengines.Transformer{}.TransformTarget(value, target)
}
