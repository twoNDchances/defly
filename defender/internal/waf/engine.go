package waf

import (
	"math"
	"strings"

	"defly-defender/ent"
)

type Engine struct {
	core Core
}

func (e Engine) TransformTarget(value any, target *ent.Target) any {
	currentDatatype := target.Datatype.String()
	if target.Edges.Pattern != nil {
		currentDatatype = target.Edges.Pattern.Datatype.String()
	}
	next := e.core.castDatatype(value, currentDatatype)
	for _, engine := range target.Edges.Engines {
		if engine == nil || engine.InputDatatype.String() != currentDatatype {
			break
		}
		next = e.applyEngine(next, engine)
		currentDatatype = engine.OutputDatatype.String()
	}
	return next
}

func (e Engine) applyEngine(value any, engine *ent.Engine) any {
	if engine == nil {
		return value
	}
	cfg := engine.Configurations
	switch engine.Type {
	case "toString":
		return e.core.stringify(value)
	case "lower":
		return strings.ToLower(e.core.stringify(value))
	case "upper":
		return strings.ToUpper(e.core.stringify(value))
	case "capitalize":
		text := e.core.stringify(value)
		if text == "" {
			return text
		}
		return strings.ToUpper(text[:1]) + text[1:]
	case "trim":
		return strings.TrimSpace(e.core.stringify(value))
	case "trimLeft":
		return strings.TrimLeftFunc(e.core.stringify(value), func(r rune) bool { return r == ' ' || r == '\t' || r == '\n' || r == '\r' })
	case "trimRight":
		return strings.TrimRightFunc(e.core.stringify(value), func(r rune) bool { return r == ' ' || r == '\t' || r == '\n' || r == '\r' })
	case "removeWhitespace":
		return strings.Join(strings.Fields(e.core.stringify(value)), "")
	case "length":
		return float64(len(e.core.stringify(value)))
	case "split":
		return strings.Split(e.core.stringify(value), e.core.stringConfig(cfg, "separator", ","))
	case "merge":
		return strings.Join(e.core.toStrings(value), e.core.stringConfig(cfg, "separator", ","))
	case "indexOf":
		items := e.core.toAnySlice(value)
		pos := int(e.core.numberConfig(cfg, "position", 0))
		if pos < 0 || pos >= len(items) {
			return nil
		}
		return items[pos]
	case "addition":
		return e.core.toFloat(value) + e.core.numberConfig(cfg, "digit", 0)
	case "subtraction":
		return e.core.toFloat(value) - e.core.numberConfig(cfg, "digit", 0)
	case "multiplication":
		return e.core.toFloat(value) * e.core.numberConfig(cfg, "digit", 1)
	case "division":
		digit := e.core.numberConfig(cfg, "digit", 1)
		if digit == 0 {
			return nil
		}
		return e.core.toFloat(value) / digit
	case "powerOf":
		return math.Pow(e.core.toFloat(value), e.core.numberConfig(cfg, "digit", 1))
	case "remainder":
		digit := e.core.numberConfig(cfg, "digit", 1)
		if digit == 0 {
			return nil
		}
		return math.Mod(e.core.toFloat(value), digit)
	case "hash":
		return e.core.hashString(e.core.stringify(value), e.core.stringConfig(cfg, "hash_method", "sha256"))
	default:
		return e.core.castDatatype(value, engine.OutputDatatype.String())
	}
}
