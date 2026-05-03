package action

import (
	"log"
	"net/http"
	"os"
	"path/filepath"
)

type Deny struct{}

func (Deny) Apply(result *Result, decision Decision) {
	config := decision.ConfigurationsValue()
	directive := stringConfig(config, "directive", "use_default")
	result.Deny = true
	result.Status = int(numberConfig(config, "status", http.StatusForbidden))
	result.ContentType = denyContentType(config)
	result.Body = defaultDenyBody(config)
	if directive == "use_default" {
		result.Status = http.StatusForbidden
		result.ContentType = "text/html; charset=utf-8"
	}
	stopDirection(result, decision.DirectionValue())
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
