package waf

import (
	"net/http"

	"defly-defender/ent"
	entdecision "defly-defender/ent/decision"
)

type PhaseRunner interface {
	Run(tx *Transaction, phase int)
}

type TargetExtractor interface {
	Extract(tx *Transaction, target *ent.Target, phase int) any
}

type TargetTransformer interface {
	TransformTarget(value any, target *ent.Target) any
}

type RuleComparator interface {
	Match(tx *Transaction, rule *ent.Rule, phase int) bool
}

type ActionExecutor interface {
	Execute(tx *Transaction, actions []*ent.Action)
}

type DecisionExecutor interface {
	Run(tx *Transaction, direction entdecision.Direction)
	ApplyRequest(tx *Transaction, writer http.ResponseWriter) bool
	ApplyResponse(tx *Transaction) error
}
