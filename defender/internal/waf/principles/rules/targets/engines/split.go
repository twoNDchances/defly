package engines

import "strings"

type Split struct {
	Separator string
}

func (s Split) Transform(value any) any {
	return strings.Split(stringify(value), s.Separator)
}
