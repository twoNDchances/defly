package action

import entdecision "defly-defender/ent/decision"

type Allow struct {
	Direction entdecision.Direction
}

func (a Allow) Apply(result *Result) {
	result.Allow = true
	stopDirection(result, a.Direction)
}
