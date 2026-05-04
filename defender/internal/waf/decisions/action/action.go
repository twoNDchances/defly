package action

import (
	"net/http"

	entdecision "defly-defender/ent/decision"
)

type Decision interface {
	DirectionValue() entdecision.Direction
	ActionValue() entdecision.Action
	ConfigurationsValue() map[string]any
}

type Transaction interface {
	ResultState() *Result
	ScoreValue() float64
	LevelValue() int
	RequestObject() *http.Request
	ResponseObject() *http.Response
	RawRequest() []byte
	RequestBodyBytes() []byte
	ResponseBodyBytes() []byte
	RequestContentType() string
	ResponseContentType() string
}

type DirectionExecutor interface {
	Apply(tx Transaction, decision Decision)
}

type Executor struct {
	Request  DirectionExecutor
	Response DirectionExecutor
}

func (e Executor) Apply(tx Transaction, decision Decision) {
	if tx == nil || decision == nil || tx.ResultState() == nil {
		return
	}
	switch decision.ActionValue() {
	case entdecision.ActionAllow:
		Allow{Direction: decision.DirectionValue()}.Apply(tx.ResultState())
	case entdecision.ActionDeny:
		NewDeny(decision).Apply(tx.ResultState())
	case entdecision.ActionRewriteHeaders:
		NewRewriteHeaders(decision.ConfigurationsValue()).Apply(tx.ResultState())
	case entdecision.ActionRewriteBody:
		NewRewriteBody(decision).Apply(tx)
	case entdecision.ActionRedirect, entdecision.ActionCancel, entdecision.ActionRewrite, entdecision.ActionSave:
		if e.Request != nil {
			e.Request.Apply(tx, decision)
		}
	case entdecision.ActionEraseCookies, entdecision.ActionForceNoCache:
		if e.Response != nil {
			e.Response.Apply(tx, decision)
		}
	}
}

func stopDirection(result *Result, direction entdecision.Direction) {
	switch direction {
	case entdecision.DirectionRequest:
		result.StopRequestDecisions = true
	case entdecision.DirectionResponse:
		result.StopResponseDecisions = true
	}
}

type Stopper struct{}

func (Stopper) All(result *Result) {
	if result == nil {
		return
	}
	result.StopRequestDecisions = true
	result.StopResponseDecisions = true
}
