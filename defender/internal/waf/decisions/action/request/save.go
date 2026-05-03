package request

import (
	"encoding/json"
	"log"
	"os"
	"path/filepath"
	"time"

	entdecision "defly-defender/ent/decision"
	decisionaction "defly-defender/internal/waf/decisions/action"
)

type Save struct{}

func (Save) Apply(tx decisionaction.Transaction, direction entdecision.Direction, config map[string]any) {
	name := filepath.Base(stringConfig(config, "name", "request.json"))
	if name == "." || name == string(filepath.Separator) {
		name = "request.json"
	}
	position := stringConfig(config, "position", "prefix")
	filename := rawFilename(name, position)
	path := filepath.Join("storage", "raw", filename)
	if err := os.MkdirAll(filepath.Dir(path), 0755); err != nil {
		log.Println("waf save decision could not create raw directory:", err)
		return
	}
	content, err := json.MarshalIndent(rawPayload(tx, direction), "", "  ")
	if err != nil {
		log.Println("waf save decision could not encode raw payload:", err)
		return
	}
	if err := os.WriteFile(path, content, 0644); err != nil {
		log.Println("waf save decision could not write raw file:", err)
	}
}

func rawFilename(name string, position string) string {
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

func rawPayload(tx decisionaction.Transaction, direction entdecision.Direction) map[string]any {
	payload := map[string]any{
		"direction": direction.String(),
		"saved_at":  time.Now().Format(time.RFC3339),
		"score":     tx.ScoreValue(),
		"level":     tx.LevelValue(),
	}
	if request := tx.RequestObject(); request != nil {
		payload["request"] = map[string]any{
			"method":  request.Method,
			"url":     request.URL.String(),
			"headers": request.Header,
			"raw":     string(tx.RawRequest()),
		}
	}
	return payload
}
