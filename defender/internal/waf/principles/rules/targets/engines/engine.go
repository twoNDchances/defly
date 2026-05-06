package engines

import "defly-defender/ent"

type Engine interface {
	Transform(value any) any
}

type Transformer struct{}

func (Transformer) TransformTarget(value any, target *ent.Target) any {
	return (Transformer{}).TraceTarget(value, target).FinalValue
}

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

func (Transformer) TraceTarget(value any, target *ent.Target) TransformTrace {
	if target == nil {
		return TransformTrace{
			InitialValue: value,
			CastedValue:  value,
			FinalValue:   value,
		}
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
