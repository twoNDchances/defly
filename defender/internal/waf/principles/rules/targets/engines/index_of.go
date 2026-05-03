package engines

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
