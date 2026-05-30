package firewall

import (
	"context"
	"net/http"

	"defly-defender/ent"
	entdecision "defly-defender/ent/decision"
	"defly-defender/internal/firewall/actions"
	"defly-defender/internal/firewall/decisions"
	"defly-defender/internal/firewall/engines"
	"defly-defender/internal/firewall/phases"
	"defly-defender/internal/firewall/rules"
	"defly-defender/internal/firewall/targets"
	"defly-defender/internal/firewall/wordlists"
)

const (
	PhaseFullRequest      = 1
	PhaseRequestHead      = 2
	PhaseRequestBody      = 3
	PhaseResponseHead     = 4
	PhaseResponseBody     = 5
	PhaseFullResponse     = 6
	transactionContextKey = "defly_firewall_transaction"
)

type Factory struct{}

type Core struct {
	Runtime Runtime
}

func New(runtime Runtime) Core {
	return Core{Runtime: runtime}
}

func (Factory) New(runtime Runtime) Core {
	return New(runtime)
}

func (c Core) NewTransaction(request *http.Request) (*Transaction, error) {
	tx := c.NewBlankTransaction(request)
	return tx, tx.CaptureRequest()
}

func (c Core) NewBlankTransaction(request *http.Request) *Transaction {
	return &Transaction{
		Runtime:     c.Runtime,
		Request:     request,
		Level:       c.Runtime.level(),
		Vars:        make(map[string]any),
		ReportReady: make(chan struct{}),
	}
}

func (c Core) RunRequest(tx *Transaction) {
	c.runPhase(tx, PhaseFullRequest)
	c.runPhase(tx, PhaseRequestHead)
	c.runPhase(tx, PhaseRequestBody)
	c.runDecisions(tx, entdecision.DirectionRequest)
}

func (c Core) RunResponse(tx *Transaction, response *http.Response) error {
	if err := tx.CaptureResponse(response); err != nil {
		tx.MarkReportReady()
		return err
	}
	c.runPhase(tx, PhaseResponseHead)
	c.runPhase(tx, PhaseResponseBody)
	c.runPhase(tx, PhaseFullResponse)
	c.runDecisions(tx, entdecision.DirectionResponse)
	err := decisions.Applier{}.ApplyResponse(tx)
	tx.MarkReportReady()
	return err
}

func (c Core) ApplyRequest(tx *Transaction, writer http.ResponseWriter) bool {
	return decisions.Applier{}.ApplyRequest(tx, writer)
}

func (c Core) SetTransaction(request *http.Request, tx *Transaction) {
	if request == nil || tx == nil {
		return
	}
	*request = *request.WithContext(context.WithValue(request.Context(), transactionContextKey, tx))
}

func (c Core) TransactionFrom(request *http.Request) *Transaction {
	if request == nil {
		return nil
	}
	tx, _ := request.Context().Value(transactionContextKey).(*Transaction)
	return tx
}

func (c Core) runPhase(tx *Transaction, phase int) {
	runtime := c.runtimeFor(tx)
	phases.Runner{
		Principles: runtime.Principles,
		Rules: phaseRules{
			tx: tx,
			evaluator: rules.Evaluator{
				Targets:  targets.Extractor{Wordlist: wordlists.Loader{}},
				Engines:  engines.Transformer{},
				Wordlist: wordlists.Loader{},
			},
		},
		Actions: phaseActions{
			tx: tx,
			executor: actions.Executor{
				Severity:          runtime.Severity,
				ReportDatabaseDSN: runtime.ReportDatabaseDSN,
				ReportDefenderID:  runtime.ReportDefenderID,
			},
		},
	}.Run(tx, phase)
}

func (c Core) runDecisions(tx *Transaction, direction entdecision.Direction) {
	runtime := c.runtimeFor(tx)
	decisions.Runner{
		Decisions:      decisions.FromEntities(runtime.Decisions),
		ViolationScore: runtime.ViolationScore,
	}.Run(tx, direction)
}

func (c Core) runtimeFor(tx *Transaction) Runtime {
	if tx != nil {
		return tx.Runtime
	}
	return c.Runtime
}

type phaseRules struct {
	tx        *Transaction
	evaluator rules.Evaluator
}

func (p phaseRules) Match(rule *ent.Rule, phase int) bool {
	return p.evaluator.Match(p.tx, rule, phase)
}

type phaseActions struct {
	tx       *Transaction
	executor actions.Executor
}

func (p phaseActions) Execute(rule *ent.Rule, items []*ent.Action) {
	p.executor.Execute(p.tx, rule, items)
}
