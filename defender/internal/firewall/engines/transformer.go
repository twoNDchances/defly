package engines

import (
	"crypto/md5"
	"crypto/sha1"
	"crypto/sha256"
	"crypto/sha512"
	"encoding/hex"
	"encoding/json"
	"fmt"
	"math"
	"strconv"
	"strings"

	"defly-defender/ent"
)

type Engine interface {
	Transform(value any) any
}

type Transformer struct{}

type TransformTrace struct {
	InitialDatatype string          `json:"initial_datatype"`
	InitialValue    any             `json:"initial_value"`
	CastedValue     any             `json:"casted_value"`
	Steps           []TransformStep `json:"steps"`
	FinalDatatype   string          `json:"final_datatype"`
	FinalValue      any             `json:"final_value"`
}

type TransformStep struct {
	ID             string `json:"id"`
	Name           string `json:"name"`
	Type           string `json:"type"`
	InputDatatype  string `json:"input_datatype"`
	OutputDatatype string `json:"output_datatype"`
	Input          any    `json:"input"`
	Output         any    `json:"output"`
}

func (Transformer) TransformTarget(value any, target *ent.Target) any {
	return (Transformer{}).TraceTarget(value, target).FinalValue
}

func (Transformer) TraceTarget(value any, target *ent.Target) TransformTrace {
	if target == nil {
		return TransformTrace{InitialValue: value, CastedValue: value, FinalValue: value}
	}
	currentDatatype := target.Datatype.String()
	if target.Edges.Pattern != nil {
		currentDatatype = target.Edges.Pattern.Datatype.String()
	}
	initialDatatype := currentDatatype
	next := castDatatype(value, currentDatatype)
	trace := TransformTrace{
		InitialDatatype: initialDatatype,
		InitialValue:    value,
		CastedValue:     next,
		FinalDatatype:   currentDatatype,
		FinalValue:      next,
	}
	for _, engine := range target.Edges.Engines {
		if engine == nil || engine.InputDatatype.String() != currentDatatype {
			break
		}
		input := next
		next = build(engine).Transform(next)
		trace.Steps = append(trace.Steps, TransformStep{
			ID:             engine.ID.String(),
			Name:           engine.Name,
			Type:           engine.Type,
			InputDatatype:  engine.InputDatatype.String(),
			OutputDatatype: engine.OutputDatatype.String(),
			Input:          input,
			Output:         next,
		})
		currentDatatype = engine.OutputDatatype.String()
		trace.FinalDatatype = currentDatatype
		trace.FinalValue = next
	}
	return trace
}

func build(engine *ent.Engine) Engine {
	if engine == nil {
		return Noop{}
	}
	cfg := engine.Configurations
	switch engine.Type {
	case "toString":
		return ToString{}
	case "lower":
		return Lower{}
	case "upper":
		return Upper{}
	case "capitalize":
		return Capitalize{}
	case "trim":
		return Trim{}
	case "trimLeft":
		return TrimLeft{}
	case "trimRight":
		return TrimRight{}
	case "removeWhitespace":
		return RemoveWhitespace{}
	case "length":
		return Length{}
	case "split":
		return Split{Separator: stringConfig(cfg, "separator", ",")}
	case "merge":
		return Merge{Separator: stringConfig(cfg, "separator", ",")}
	case "indexOf":
		return IndexOf{Position: int(numberConfig(cfg, "position", 0))}
	case "addition":
		return Addition{Digit: numberConfig(cfg, "digit", 0)}
	case "subtraction":
		return Subtraction{Digit: numberConfig(cfg, "digit", 0)}
	case "multiplication":
		return Multiplication{Digit: numberConfig(cfg, "digit", 1)}
	case "division":
		return Division{Digit: numberConfig(cfg, "digit", 1)}
	case "powerOf":
		return PowerOf{Digit: numberConfig(cfg, "digit", 1)}
	case "remainder":
		return Remainder{Digit: numberConfig(cfg, "digit", 1)}
	case "hash":
		return Hash{Method: stringConfig(cfg, "hash_method", "sha256")}
	default:
		return Cast{Datatype: engine.OutputDatatype.String()}
	}
}

type Noop struct{}

func (Noop) Transform(value any) any {
	return value
}

type Cast struct {
	Datatype string
}

func (c Cast) Transform(value any) any {
	return castDatatype(value, c.Datatype)
}

type ToString struct{}

func (ToString) Transform(value any) any {
	return stringify(value)
}

type Lower struct{}

func (Lower) Transform(value any) any {
	return strings.ToLower(stringify(value))
}

type Upper struct{}

func (Upper) Transform(value any) any {
	return strings.ToUpper(stringify(value))
}

type Capitalize struct{}

func (Capitalize) Transform(value any) any {
	text := stringify(value)
	if text == "" {
		return text
	}
	return strings.ToUpper(text[:1]) + text[1:]
}

type Trim struct{}

func (Trim) Transform(value any) any {
	return strings.TrimSpace(stringify(value))
}

type TrimLeft struct{}

func (TrimLeft) Transform(value any) any {
	return strings.TrimLeftFunc(stringify(value), isWhitespace)
}

type TrimRight struct{}

func (TrimRight) Transform(value any) any {
	return strings.TrimRightFunc(stringify(value), isWhitespace)
}

type RemoveWhitespace struct{}

func (RemoveWhitespace) Transform(value any) any {
	return strings.Join(strings.Fields(stringify(value)), "")
}

type Length struct{}

func (Length) Transform(value any) any {
	return float64(len(stringify(value)))
}

type Split struct {
	Separator string
}

func (s Split) Transform(value any) any {
	return strings.Split(stringify(value), s.Separator)
}

type Merge struct {
	Separator string
}

func (m Merge) Transform(value any) any {
	return strings.Join(toStrings(value), m.Separator)
}

type IndexOf struct {
	Position int
}

func (i IndexOf) Transform(value any) any {
	items := toAnySlice(value)
	if i.Position < 0 || i.Position >= len(items) {
		return nil
	}
	return items[i.Position]
}

type Addition struct {
	Digit float64
}

func (a Addition) Transform(value any) any {
	return toFloat(value) + a.Digit
}

type Subtraction struct {
	Digit float64
}

func (s Subtraction) Transform(value any) any {
	return toFloat(value) - s.Digit
}

type Multiplication struct {
	Digit float64
}

func (m Multiplication) Transform(value any) any {
	return toFloat(value) * m.Digit
}

type Division struct {
	Digit float64
}

func (d Division) Transform(value any) any {
	if d.Digit == 0 {
		return nil
	}
	return toFloat(value) / d.Digit
}

type PowerOf struct {
	Digit float64
}

func (p PowerOf) Transform(value any) any {
	return math.Pow(toFloat(value), p.Digit)
}

type Remainder struct {
	Digit float64
}

func (r Remainder) Transform(value any) any {
	if r.Digit == 0 {
		return nil
	}
	return math.Mod(toFloat(value), r.Digit)
}

type Hash struct {
	Method string
}

func (h Hash) Transform(value any) any {
	text := stringify(value)
	switch strings.ToLower(h.Method) {
	case "md5":
		sum := md5.Sum([]byte(text))
		return hex.EncodeToString(sum[:])
	case "sha1":
		sum := sha1.Sum([]byte(text))
		return hex.EncodeToString(sum[:])
	case "sha224":
		sum := sha256.Sum224([]byte(text))
		return hex.EncodeToString(sum[:])
	case "sha512":
		sum := sha512.Sum512([]byte(text))
		return hex.EncodeToString(sum[:])
	default:
		sum := sha256.Sum256([]byte(text))
		return hex.EncodeToString(sum[:])
	}
}

func isWhitespace(r rune) bool {
	return r == ' ' || r == '\t' || r == '\n' || r == '\r'
}

func castDatatype(value any, datatype string) any {
	switch datatype {
	case "array":
		return toAnySlice(value)
	case "number":
		return toFloat(value)
	case "string":
		return stringify(value)
	default:
		return value
	}
}

func stringify(value any) string {
	switch typed := value.(type) {
	case nil:
		return ""
	case string:
		return typed
	case []byte:
		return string(typed)
	case fmt.Stringer:
		return typed.String()
	default:
		return fmt.Sprint(typed)
	}
}

func toFloat(value any) float64 {
	switch typed := value.(type) {
	case int:
		return float64(typed)
	case int64:
		return float64(typed)
	case uint64:
		return float64(typed)
	case float32:
		return float64(typed)
	case float64:
		return typed
	case json.Number:
		number, _ := typed.Float64()
		return number
	default:
		number, _ := strconv.ParseFloat(strings.TrimSpace(stringify(value)), 64)
		return number
	}
}

func toAnySlice(value any) []any {
	switch typed := value.(type) {
	case nil:
		return []any{nil}
	case []any:
		return typed
	case []string:
		items := make([]any, 0, len(typed))
		for _, item := range typed {
			items = append(items, item)
		}
		return items
	default:
		return []any{typed}
	}
}

func toStrings(value any) []string {
	items := toAnySlice(value)
	result := make([]string, 0, len(items))
	for _, item := range items {
		result = append(result, stringify(item))
	}
	return result
}

func stringConfig(config map[string]any, key string, fallback string) string {
	if config == nil {
		return fallback
	}
	if value, ok := config[key]; ok {
		return stringify(value)
	}
	return fallback
}

func numberConfig(config map[string]any, key string, fallback float64) float64 {
	if config == nil {
		return fallback
	}
	if value, ok := config[key]; ok {
		return toFloat(value)
	}
	return fallback
}
