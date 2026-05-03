package actions

import (
	"io"
	"log"
	"net/http"
	"strings"
	"time"

	"defly-defender/ent"
	entaction "defly-defender/ent/action"
)

type Action interface {
	Execute(tx Transaction)
	Async() bool
	Validate() error
}

type Transaction interface {
	IsAllowed() bool
	IsDenied() bool
	SetAllow()
	SetDeny(status int, contentType string, body []byte)
	AddScore(score float64)
	CurrentScore() float64
	SetScore(score float64)
	CurrentLevel() int
	SetLevel(level int)
	SetVar(key string, value any)
	UnsetVar(key string)
	RequestRemoteAddr() string
	RequestMethod() string
	RequestPath() string
}

type Executor struct {
	Severity map[string]int
	Client   *http.Client
}

func (e Executor) Execute(tx Transaction, actions []*ent.Action) {
	for _, action := range actions {
		if action == nil || tx == nil || tx.IsAllowed() || tx.IsDenied() {
			return
		}
		runtimeAction, err := e.build(action)
		if err != nil {
			log.Println("waf action validation failed:", err)
		}
		if runtimeAction == nil {
			continue
		}
		if runtimeAction.Async() {
			go runtimeAction.Execute(tx)
			continue
		}
		runtimeAction.Execute(tx)
	}
}

func (e Executor) build(action *ent.Action) (Action, error) {
	cfg := action.Configurations
	var runtimeAction Action
	switch action.Type {
	case entaction.TypeAllow:
		runtimeAction = Allow{}
	case entaction.TypeDeny:
		runtimeAction = Deny{
			Status:      int(numberConfig(cfg, "status", http.StatusForbidden)),
			ContentType: denyContentType(cfg),
			Body:        []byte(stringConfig(cfg, "body", `{"message":"request denied"}`)),
		}
	case entaction.TypeLog:
		format := stringConfig(cfg, "format", "[%time%] %ip% | %method% | %path% | score=%score%")
		runtimeAction = Log{
			Render: func(tx Transaction) string {
				return renderLog(tx, format)
			},
		}
	case entaction.TypeRequest, entaction.TypeReport:
		runtimeAction = Request{Send: func() { e.sendRequest(cfg) }}
	case entaction.TypeSuspect:
		runtimeAction = Suspect{Score: float64(e.Severity[stringConfig(cfg, "severity", "notice")])}
	case entaction.TypeSetter:
		runtimeAction = Setter{
			Directive: stringConfig(cfg, "directive", "set"),
			Items:     setterItems(cfg),
		}
	case entaction.TypeScore:
		runtimeAction = Score{Value: numberConfig(cfg, "value", 0), Operator: stringConfig(cfg, "operator", "override")}
	case entaction.TypeLevel:
		runtimeAction = Level{Value: numberConfig(cfg, "value", 1), Operator: stringConfig(cfg, "operator", "override")}
	default:
		return nil, nil
	}
	return runtimeAction, runtimeAction.Validate()
}

func (e Executor) sendRequest(config map[string]any) {
	requestURL := stringConfig(config, "url", "")
	if requestURL == "" {
		return
	}
	method := strings.ToUpper(stringConfig(config, "method", http.MethodGet))
	body := strings.NewReader(stringConfig(config, "body", ""))
	request, err := http.NewRequest(method, requestURL, body)
	if err != nil {
		log.Println(err)
		return
	}
	for key, value := range keyValueMap(config, "headers") {
		request.Header.Set(key, value)
	}
	client := e.Client
	if client == nil {
		client = &http.Client{Timeout: 5 * time.Second}
	}
	response, err := client.Do(request)
	if err != nil {
		log.Println(err)
		return
	}
	_, _ = io.Copy(io.Discard, response.Body)
	_ = response.Body.Close()
}

func setterItems(config map[string]any) []SetterItem {
	items := configItems(config, "execution")
	result := make([]SetterItem, 0, len(items))
	for _, item := range items {
		datatype := stringify(item["datatype"])
		result = append(result, SetterItem{
			Key:   stringify(item["key"]),
			Value: castDatatype(item["value"], datatype),
		})
	}
	return result
}

func renderLog(tx Transaction, format string) string {
	replacer := strings.NewReplacer(
		"%time%", time.Now().Format(time.RFC3339),
		"%ip%", stringify(tx.RequestRemoteAddr()),
		"%method%", stringify(tx.RequestMethod()),
		"%path%", stringify(tx.RequestPath()),
		"%score%", floatString(tx.CurrentScore()),
	)
	return replacer.Replace(format)
}
