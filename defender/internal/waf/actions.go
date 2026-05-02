package waf

import (
	"io"
	"log"
	"net/http"
	"strings"
	"time"

	"defly-defender/ent"
	entaction "defly-defender/ent/action"
)

type Actions struct {
	core Core
}

func (a Actions) Execute(tx *Transaction, actions []*ent.Action) {
	for _, action := range actions {
		if action == nil || tx.Result.Allow || tx.Result.Deny {
			return
		}
		if action.Type == entaction.TypeRequest || action.Type == entaction.TypeReport {
			go a.execute(tx, action)
			continue
		}
		a.execute(tx, action)
	}
}

func (a Actions) execute(tx *Transaction, action *ent.Action) {
	cfg := action.Configurations
	switch action.Type {
	case entaction.TypeAllow:
		tx.Result.Allow = true
	case entaction.TypeDeny:
		tx.Result.Deny = true
		tx.Result.Status = int(a.core.numberConfig(cfg, "status", http.StatusForbidden))
		tx.Result.ContentType = a.core.denyContentType(cfg)
		tx.Result.Body = []byte(a.core.stringConfig(cfg, "body", `{"message":"request denied"}`))
	case entaction.TypeLog:
		log.Println(a.renderLog(tx, a.core.stringConfig(cfg, "format", "[%time%] %ip% | %method% | %path% | score=%score%")))
	case entaction.TypeRequest, entaction.TypeReport:
		a.sendRequest(cfg)
	case entaction.TypeSuspect:
		tx.Score += float64(a.core.Config.Severity[a.core.stringConfig(cfg, "severity", "notice")])
	case entaction.TypeSetter:
		if a.core.stringConfig(cfg, "directive", "set") == "unset" {
			for _, item := range a.core.configItems(cfg, "execution") {
				delete(tx.Vars, a.core.stringify(item["key"]))
			}
			return
		}
		for _, item := range a.core.configItems(cfg, "execution") {
			tx.Vars[a.core.stringify(item["key"])] = a.core.castDatatype(item["value"], a.core.stringify(item["datatype"]))
		}
	case entaction.TypeScore:
		tx.Score = a.core.applyBehavior(tx.Score, a.core.numberConfig(cfg, "value", 0), a.core.stringConfig(cfg, "operator", "override"))
	case entaction.TypeLevel:
		level := int(a.core.applyBehavior(float64(tx.Level), a.core.numberConfig(cfg, "value", 1), a.core.stringConfig(cfg, "operator", "override")))
		if level < 1 {
			level = 1
		}
		tx.Level = level
	}
}

func (a Actions) sendRequest(config map[string]any) {
	requestURL := a.core.stringConfig(config, "url", "")
	if requestURL == "" {
		return
	}
	method := strings.ToUpper(a.core.stringConfig(config, "method", http.MethodGet))
	body := strings.NewReader(a.core.stringConfig(config, "body", ""))
	request, err := http.NewRequest(method, requestURL, body)
	if err != nil {
		log.Println(err)
		return
	}
	for key, value := range a.core.keyValueMap(config, "headers") {
		request.Header.Set(key, value)
	}
	client := &http.Client{Timeout: 5 * time.Second}
	response, err := client.Do(request)
	if err != nil {
		log.Println(err)
		return
	}
	_, _ = io.Copy(io.Discard, response.Body)
	_ = response.Body.Close()
}

func (a Actions) renderLog(tx *Transaction, format string) string {
	replacer := strings.NewReplacer(
		"%time%", time.Now().Format(time.RFC3339),
		"%ip%", a.core.stringify(a.core.Targets().metaValue(tx, PhaseRequestHead, "ip")),
		"%method%", a.core.stringify(a.core.Targets().metaValue(tx, PhaseRequestHead, "method")),
		"%path%", a.core.stringify(a.core.Targets().metaValue(tx, PhaseRequestHead, "path")),
		"%score%", a.core.floatString(tx.Score),
	)
	return replacer.Replace(format)
}
