package decisions

import (
	"defly-defender/ent"
	entdecision "defly-defender/ent/decision"
	decisionaction "defly-defender/internal/waf/decisions/action"
)

type Action interface {
	Apply(tx decisionaction.Transaction, decision decisionaction.Decision)
}

type Decision interface {
	DirectionValue() entdecision.Direction
	ConditionValue() entdecision.Condition
	ActionValue() entdecision.Action
	ScoreValue() float64
	ConfigurationsValue() map[string]any
}

type Runner struct {
	Decisions      []Decision
	ViolationScore int
	Actions        Action
}

type entityDecision struct {
	decision *ent.Decision
}

func FromEntities(decisions []*ent.Decision) []Decision {
	result := make([]Decision, 0, len(decisions))
	for _, decision := range decisions {
		if decision != nil {
			result = append(result, entityDecision{decision: decision})
		}
	}
	return result
}

func (r Runner) Run(tx decisionaction.Transaction, direction entdecision.Direction) {
	if tx == nil || stopped(tx.ResultState(), direction) {
		return
	}
	result := tx.ResultState()
	if result == nil || result.Deny || result.Cancel {
		return
	}
	for _, decision := range r.Decisions {
		if decision == nil || decision.DirectionValue() != direction {
			continue
		}
		score := decision.ScoreValue()
		if score == 0 && r.ViolationScore > 0 {
			score = float64(r.ViolationScore)
		}
		if !matches(decision.ConditionValue(), tx.ScoreValue(), score) {
			continue
		}
		r.Actions.Apply(tx, decision)
		if result.Deny || result.Cancel || stopped(result, direction) {
			return
		}
	}
}

func (d entityDecision) DirectionValue() entdecision.Direction {
	return d.decision.Direction
}

func (d entityDecision) ConditionValue() entdecision.Condition {
	return d.decision.Condition
}

func (d entityDecision) ActionValue() entdecision.Action {
	return d.decision.Action
}

func (d entityDecision) ScoreValue() float64 {
	return d.decision.Score
}

func (d entityDecision) ConfigurationsValue() map[string]any {
	return d.decision.Configurations
}

func stopped(result *decisionaction.Result, direction entdecision.Direction) bool {
	if result == nil {
		return true
	}
	switch direction {
	case entdecision.DirectionRequest:
		return result.StopRequestDecisions
	case entdecision.DirectionResponse:
		return result.StopResponseDecisions
	default:
		return false
	}
}

func matches(condition entdecision.Condition, actual float64, expected float64) bool {
	switch condition {
	case entdecision.ConditionLessThanOrEqual:
		return actual <= expected
	case entdecision.ConditionLessThan:
		return actual < expected
	case entdecision.ConditionEqual:
		return actual == expected
	case entdecision.ConditionGreaterThanOrEqual:
		return actual >= expected
	case entdecision.ConditionGreaterThan:
		return actual > expected
	default:
		return false
	}
}
