package engines

type Merge struct {
	Separator string
}

func (m Merge) Transform(value any) any {
	return joinStrings(toStrings(value), m.Separator)
}
