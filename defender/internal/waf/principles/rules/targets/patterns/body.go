package patterns

type Body struct{}

func (Body) RequestBodyKeys(ctx Context) any {
	return mapKeys(requestBodyFields(ctx))
}

func (Body) ResponseBodyKeys(ctx Context) any {
	return mapKeys(responseBodyFields(ctx))
}

func (Body) RequestBodyValues(ctx Context) any {
	return mapValues(requestBodyFields(ctx))
}

func (Body) ResponseBodyValues(ctx Context) any {
	return mapValues(responseBodyFields(ctx))
}

func (Body) RequestBodySize(ctx Context) any {
	return float64(len(requestBodyFields(ctx)))
}

func (Body) ResponseBodySize(ctx Context) any {
	return float64(len(responseBodyFields(ctx)))
}

func (Body) RequestBodyLength(ctx Context) any {
	return float64(len(ctx.RequestBodyBytes()))
}

func (Body) ResponseBodyLength(ctx Context) any {
	return float64(len(ctx.ResponseBodyBytes()))
}
