package engines

import "defly-defender/ent"

type Engine interface {
	Transform(value any) any
}

type Transformer struct{}

func (Transformer) TransformTarget(value any, target *ent.Target) any {
	if target == nil {
		return value
	}
	currentDatatype := target.Datatype.String()
	if target.Edges.Pattern != nil {
		currentDatatype = target.Edges.Pattern.Datatype.String()
	}
	next := castDatatype(value, currentDatatype)
	for _, engine := range target.Edges.Engines {
		if engine == nil || engine.InputDatatype.String() != currentDatatype {
			break
		}
		next = build(engine).Transform(next)
		currentDatatype = engine.OutputDatatype.String()
	}
	return next
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
