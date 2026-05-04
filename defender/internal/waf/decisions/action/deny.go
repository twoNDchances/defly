package action

import (
	entdecision "defly-defender/ent/decision"
	"log"
	"net/http"
	"os"
	"path/filepath"
)

type Deny struct {
	Direction   entdecision.Direction
	Status      int
	ContentType string
	Body        []byte
}

func NewDeny(decision Decision) Deny {
	config := decision.ConfigurationsValue()
	directive := stringConfig(config, "directive", "use_default")
	action := Deny{
		Direction:   decision.DirectionValue(),
		Status:      int(numberConfig(config, "status", http.StatusForbidden)),
		ContentType: denyContentType(config),
		Body:        defaultDenyBody(config),
	}
	if directive == "use_default" {
		action.Status = http.StatusForbidden
		action.ContentType = "text/html; charset=utf-8"
	}
	return action
}

func (a Deny) Apply(result *Result) {
	result.Deny = true
	result.Status = a.Status
	result.ContentType = a.ContentType
	result.Body = a.Body
	stopDirection(result, a.Direction)
}

func defaultDenyBody(config map[string]any) []byte {
	if stringConfig(config, "directive", "use_default") == "use_default" {
		body, err := os.ReadFile(filepath.Join("resources", "views", "403.html"))
		if err == nil {
			return body
		}
		log.Println(err)
		return []byte("<!DOCTYPE html><html><body><h1>403 - Forbidden</h1></body></html>")
	}
	body := stringConfig(config, "body", "")
	if body == "" {
		body = `{"message":"request denied"}`
	}
	return []byte(body)
}
