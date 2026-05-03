package patterns

type Query struct{}

func (Query) RequestQueryKeys(ctx Context) any {
	return queryKeys(ctx.RequestQuery())
}

func (Query) RequestQueryValues(ctx Context) any {
	return queryValues(ctx.RequestQuery())
}

func (Query) RequestQuerySize(ctx Context) any {
	return float64(len(ctx.RequestQuery()))
}
