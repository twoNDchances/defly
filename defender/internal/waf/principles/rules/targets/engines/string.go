package engines

import "strings"

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

func isWhitespace(r rune) bool {
	return r == ' ' || r == '\t' || r == '\n' || r == '\r'
}
