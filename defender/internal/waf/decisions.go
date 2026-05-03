package waf

import (
	"net/http"

	entdecision "defly-defender/ent/decision"
	"defly-defender/internal/globals"
	decisionruntime "defly-defender/internal/waf/decisions"
	decisionaction "defly-defender/internal/waf/decisions/action"
	decisionrequest "defly-defender/internal/waf/decisions/action/request"
	decisionresponse "defly-defender/internal/waf/decisions/action/response"
)

type Decisions struct {
	core Core
}

func (d Decisions) Run(tx *Transaction, direction entdecision.Direction) {
	decisionruntime.Runner{
		Decisions:      decisionruntime.FromEntities(globals.Decisions),
		ViolationScore: d.core.Config.ViolationScore,
		Actions: decisionaction.Executor{
			Request:  decisionrequest.Executor{},
			Response: decisionresponse.Executor{},
		},
	}.Run(tx, direction)
}

func (d Decisions) ApplyRequest(tx *Transaction, writer http.ResponseWriter) bool {
	return decisionrequest.Applier{}.Apply(tx, writer)
}

func (d Decisions) ApplyResponse(tx *Transaction) error {
	return decisionresponse.Applier{}.Apply(tx)
}
