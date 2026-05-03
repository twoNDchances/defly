package action

import entdecision "defly-defender/ent/decision"

type Allow struct{}

func (Allow) Apply(result *Result, direction entdecision.Direction) {
	result.Allow = true
	stopDirection(result, direction)
}
