package waf

import (
	"context"
	"net/http"

	entdecision "defly-defender/ent/decision"
)

const (
	PhaseFullRequest      = 1
	PhaseRequestHead      = 2
	PhaseRequestBody      = 3
	PhaseResponseHead     = 4
	PhaseResponseBody     = 5
	PhaseFullResponse     = 6
	transactionContextKey = "defly_waf_transaction"
)

type Factory struct{}

type Core struct {
	Config Config
}

func (Factory) New(config Config) Core {
	return Core{Config: config}
}

func (e Core) Phases() Phases {
	return Phases{core: e}
}

func (e Core) Rules() Rules {
	return Rules{core: e}
}

func (e Core) Targets() Targets {
	return Targets{core: e}
}

func (e Core) Engine() Engine {
	return Engine{core: e}
}

func (e Core) Actions() Actions {
	return Actions{core: e}
}

func (e Core) Decisions() Decisions {
	return Decisions{core: e}
}

func (e Core) NewTransaction(request *http.Request) (*Transaction, error) {
	tx := e.NewBlankTransaction(request)
	return tx, tx.CaptureRequest()
}

func (e Core) NewBlankTransaction(request *http.Request) *Transaction {
	level := e.Config.ViolationLevel
	if level < 1 {
		level = 1
	}
	return &Transaction{
		Request:     request,
		Level:       level,
		Vars:        make(map[string]any),
		ReportReady: make(chan struct{}),
	}
}

func (e Core) RunRequest(tx *Transaction) {
	e.Phases().Run(tx, PhaseFullRequest)
	e.Phases().Run(tx, PhaseRequestHead)
	e.Phases().Run(tx, PhaseRequestBody)
	e.Decisions().Run(tx, entdecision.DirectionRequest)
}

func (e Core) RunResponse(tx *Transaction, response *http.Response) error {
	if err := tx.CaptureResponse(response); err != nil {
		tx.MarkReportReady()
		return err
	}
	e.Phases().Run(tx, PhaseResponseHead)
	e.Phases().Run(tx, PhaseResponseBody)
	e.Phases().Run(tx, PhaseFullResponse)
	e.Decisions().Run(tx, entdecision.DirectionResponse)
	err := e.Decisions().ApplyResponse(tx)
	tx.MarkReportReady()
	return err
}

func (e Core) SetTransaction(request *http.Request, tx *Transaction) {
	if request == nil || tx == nil {
		return
	}
	*request = *request.WithContext(context.WithValue(request.Context(), transactionContextKey, tx))
}

func (e Core) TransactionFrom(request *http.Request) *Transaction {
	if request == nil {
		return nil
	}
	tx, _ := request.Context().Value(transactionContextKey).(*Transaction)
	return tx
}
