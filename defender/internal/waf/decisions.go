package waf

import (
	"encoding/json"
	"fmt"
	"log"
	"net/http"
	"net/url"
	"os"
	"path/filepath"
	"time"

	"defly-defender/ent"
	entdecision "defly-defender/ent/decision"
	"defly-defender/internal/globals"
)

type Decisions struct {
	core Core
}

func (d Decisions) Run(tx *Transaction, direction entdecision.Direction) {
	if tx == nil || tx.Result.Deny || tx.Result.Cancel || d.stopped(tx, direction) {
		return
	}
	for _, decision := range globals.Decisions {
		if decision == nil || decision.Direction != direction {
			continue
		}
		score := decision.Score
		if score == 0 && d.core.Config.ViolationScore > 0 {
			score = float64(d.core.Config.ViolationScore)
		}
		if !d.matches(decision.Condition, tx.Score, score) {
			continue
		}
		d.apply(tx, decision)
		if tx.Result.Deny || tx.Result.Cancel || d.stopped(tx, direction) {
			return
		}
	}
}

func (d Decisions) apply(tx *Transaction, decision *ent.Decision) {
	cfg := decision.Configurations
	switch decision.Action {
	case entdecision.ActionAllow:
		tx.Result.Allow = true
		d.stopDirection(tx, decision.Direction)
	case entdecision.ActionDeny, entdecision.ActionCancel:
		if decision.Action == entdecision.ActionCancel {
			d.applyCancel(tx)
		} else {
			d.applyDeny(tx, cfg)
			d.stopDirection(tx, decision.Direction)
		}
	case entdecision.ActionRewriteHeaders:
		d.core.applyHeaderDirective(&tx.Result, cfg)
	case entdecision.ActionRewriteBody:
		body, contentType := d.bodyContext(tx, decision.Direction)
		if rewritten, ok := d.core.rewriteBody(body, contentType, cfg); ok {
			tx.Result.BodyRewrite = rewritten
			tx.Result.BodyRewritten = true
		}
	case entdecision.ActionRedirect:
		tx.Result.RedirectURL = d.core.stringConfig(cfg, "url", "")
		d.stopAll(tx)
	case entdecision.ActionRewrite:
		d.applyRewrite(tx, cfg)
	case entdecision.ActionEraseCookies:
		tx.Result.EraseCookies = true
	case entdecision.ActionForceNoCache:
		tx.Result.ForceNoCache = true
	case entdecision.ActionSave:
		d.saveRaw(tx, decision.Direction, cfg)
	}
}

func (d Decisions) stopped(tx *Transaction, direction entdecision.Direction) bool {
	switch direction {
	case entdecision.DirectionRequest:
		return tx.Result.StopRequestDecisions
	case entdecision.DirectionResponse:
		return tx.Result.StopResponseDecisions
	default:
		return false
	}
}

func (d Decisions) stopDirection(tx *Transaction, direction entdecision.Direction) {
	switch direction {
	case entdecision.DirectionRequest:
		tx.Result.StopRequestDecisions = true
	case entdecision.DirectionResponse:
		tx.Result.StopResponseDecisions = true
	}
}

func (d Decisions) stopAll(tx *Transaction) {
	tx.Result.StopRequestDecisions = true
	tx.Result.StopResponseDecisions = true
}

func (d Decisions) applyCancel(tx *Transaction) {
	tx.Result.Cancel = true
	d.stopAll(tx)
}

func (d Decisions) applyDeny(tx *Transaction, config map[string]any) {
	directive := d.core.stringConfig(config, "directive", "use_default")
	tx.Result.Deny = true
	tx.Result.Status = int(d.core.numberConfig(config, "status", http.StatusForbidden))
	tx.Result.ContentType = d.core.denyContentType(config)
	tx.Result.Body = d.defaultDenyBody(config)
	if directive == "use_default" {
		tx.Result.Status = http.StatusForbidden
		tx.Result.ContentType = "text/html; charset=utf-8"
	}
}

func (d Decisions) bodyContext(tx *Transaction, direction entdecision.Direction) ([]byte, string) {
	if direction == entdecision.DirectionResponse && tx.Response != nil {
		return tx.ResponseBody, tx.Response.Header.Get("Content-Type")
	}
	if tx.Request != nil {
		return tx.RequestBody, tx.Request.Header.Get("Content-Type")
	}
	return nil, ""
}

func (d Decisions) applyRewrite(tx *Transaction, config map[string]any) {
	if d.core.stringConfig(config, "type", "path") == "query" {
		query, _ := config["query"].(map[string]any)
		if query == nil {
			query = config
		}
		if d.core.stringConfig(query, "directive", "set") == "unset" {
			tx.Result.UnsetQuery = append(tx.Result.UnsetQuery, d.core.executionKeys(query, "unset")...)
			return
		}
		if tx.Result.RewriteQuery == nil {
			tx.Result.RewriteQuery = make(map[string]string)
		}
		for key, value := range d.core.executionKeyValueMap(query, "set") {
			tx.Result.RewriteQuery[key] = value
		}
		return
	}
	tx.Result.RewritePath = d.core.stringConfig(config, "path", "")
}

func (d Decisions) defaultDenyBody(config map[string]any) []byte {
	if d.core.stringConfig(config, "directive", "use_default") == "use_default" {
		body, err := os.ReadFile(filepath.Join("resources", "views", "403.html"))
		if err == nil {
			return body
		}
		log.Println(err)
		return []byte("<!DOCTYPE html><html><body><h1>403 - Forbidden</h1></body></html>")
	}
	body := d.core.stringConfig(config, "body", "")
	if body == "" {
		body = `{"message":"request denied"}`
	}
	return []byte(body)
}

func (d Decisions) ApplyRequest(tx *Transaction, writer http.ResponseWriter) bool {
	if tx == nil {
		return false
	}
	d.applyRequestMutation(tx)
	if tx.Result.Cancel {
		if conn, _, err := http.NewResponseController(writer).Hijack(); err == nil {
			_ = conn.Close()
		} else {
			log.Println("waf cancel decision could not close connection:", err)
		}
		return true
	}
	if !tx.Result.Deny {
		return false
	}
	status := tx.Result.Status
	if status == 0 {
		status = http.StatusForbidden
	}
	contentType := tx.Result.ContentType
	if contentType == "" {
		contentType = "application/json"
	}
	body := tx.Result.Body
	if len(body) == 0 {
		body = []byte(`{"message":"request denied"}`)
	}
	writer.Header().Set("Content-Type", contentType)
	writer.WriteHeader(status)
	_, _ = writer.Write(body)
	return true
}

func (d Decisions) saveRaw(tx *Transaction, direction entdecision.Direction, config map[string]any) {
	name := filepath.Base(d.core.stringConfig(config, "name", "request.json"))
	if name == "." || name == string(filepath.Separator) {
		name = "request.json"
	}
	position := d.core.stringConfig(config, "position", "prefix")
	filename := d.rawFilename(name, position)
	path := filepath.Join("storage", "raw", filename)
	if err := os.MkdirAll(filepath.Dir(path), 0755); err != nil {
		log.Println("waf save decision could not create raw directory:", err)
		return
	}
	payload := d.rawPayload(tx, direction)
	content, err := json.MarshalIndent(payload, "", "  ")
	if err != nil {
		log.Println("waf save decision could not encode raw payload:", err)
		return
	}
	if err := os.WriteFile(path, content, 0644); err != nil {
		log.Println("waf save decision could not write raw file:", err)
	}
}

func (d Decisions) rawFilename(name string, position string) string {
	stamp := time.Now().Format("20060102-150405")
	extension := filepath.Ext(name)
	base := name
	if extension == "" {
		extension = ".json"
	} else {
		base = name[:len(name)-len(extension)]
	}
	if base == "" {
		base = "request"
	}
	if position == "suffix" {
		return stamp + base + extension
	}
	return base + stamp + extension
}

func (d Decisions) rawPayload(tx *Transaction, direction entdecision.Direction) map[string]any {
	payload := map[string]any{
		"direction": direction.String(),
		"saved_at":  time.Now().Format(time.RFC3339),
		"score":     tx.Score,
		"level":     tx.Level,
	}
	if tx.Request != nil {
		payload["request"] = map[string]any{
			"method":  tx.Request.Method,
			"url":     tx.Request.URL.String(),
			"headers": tx.Request.Header,
			"raw":     string(tx.RequestRaw),
		}
	}
	if direction == entdecision.DirectionResponse && tx.Response != nil {
		payload["response"] = map[string]any{
			"status":  tx.Response.StatusCode,
			"headers": tx.Response.Header,
			"raw":     string(tx.ResponseRaw),
		}
	}
	return payload
}

func (d Decisions) applyRequestMutation(tx *Transaction) {
	if tx.Request == nil {
		return
	}
	if tx.Result.RewritePath != "" {
		tx.Request.URL.Path = tx.Result.RewritePath
	}
	if len(tx.Result.RewriteQuery) > 0 || len(tx.Result.UnsetQuery) > 0 {
		query := tx.Request.URL.Query()
		for key, value := range tx.Result.RewriteQuery {
			query.Set(key, value)
		}
		for _, key := range tx.Result.UnsetQuery {
			query.Del(key)
		}
		tx.Request.URL.RawQuery = query.Encode()
	}
	for key, values := range tx.Result.RewriteHeaders {
		tx.Request.Header.Del(key)
		for _, value := range values {
			tx.Request.Header.Add(key, value)
		}
	}
	for _, key := range tx.Result.UnsetHeaders {
		tx.Request.Header.Del(key)
	}
	if tx.Result.BodyRewritten {
		tx.SetRequestBody(tx.Result.BodyRewrite)
	}
	if tx.Result.RedirectURL != "" {
		if parsed, err := url.Parse(tx.Result.RedirectURL); err == nil {
			tx.Request.URL = parsed
			tx.Request.Host = parsed.Host
		}
	}
}

func (d Decisions) ApplyResponse(tx *Transaction) error {
	if tx == nil || tx.Response == nil {
		return nil
	}
	if tx.Result.Deny {
		status := tx.Result.Status
		if status == 0 {
			status = http.StatusForbidden
		}
		tx.Response.StatusCode = status
		tx.Response.Status = fmt.Sprintf("%d %s", status, http.StatusText(status))
		tx.Response.Header = http.Header{}
		tx.Response.Header.Set("Content-Type", tx.Result.ContentType)
		if tx.Result.ContentType == "" {
			tx.Response.Header.Set("Content-Type", "application/json")
		}
		body := tx.Result.Body
		if len(body) == 0 {
			body = []byte(`{"message":"response denied"}`)
		}
		tx.SetResponseBody(body)
		return nil
	}
	for key, values := range tx.Result.RewriteHeaders {
		tx.Response.Header.Del(key)
		for _, value := range values {
			tx.Response.Header.Add(key, value)
		}
	}
	for _, key := range tx.Result.UnsetHeaders {
		tx.Response.Header.Del(key)
	}
	if tx.Result.ForceNoCache {
		tx.Response.Header.Set("Cache-Control", "no-store, no-cache, must-revalidate, max-age=0")
		tx.Response.Header.Set("Pragma", "no-cache")
		tx.Response.Header.Set("Expires", "0")
	}
	if tx.Result.EraseCookies {
		tx.Response.Header.Del("Set-Cookie")
	}
	if tx.Result.BodyRewritten {
		tx.SetResponseBody(tx.Result.BodyRewrite)
	}
	return nil
}

func (d Decisions) matches(condition entdecision.Condition, actual float64, expected float64) bool {
	switch condition {
	case entdecision.ConditionLessThanOrEqual:
		return actual <= expected
	case entdecision.ConditionLessThan:
		return actual < expected
	case entdecision.ConditionEqual:
		return actual == expected
	case entdecision.ConditionGreaterThanOrEqual:
		return actual >= expected
	case entdecision.ConditionGreaterThan:
		return actual > expected
	default:
		return false
	}
}
