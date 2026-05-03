package patterns

type Full struct{}

func (Full) RequestFull(ctx Context) any {
	return string(ctx.RawRequest())
}

func (Full) ResponseFull(ctx Context) any {
	return string(ctx.RawResponse())
}

func (Full) RequestFullHeaders(ctx Context) any {
	return headersString(ctx.RequestHeaders())
}

func (Full) ResponseFullHeaders(ctx Context) any {
	return headersString(ctx.ResponseHeaders())
}

func (Full) RequestFullBody(ctx Context) any {
	return string(ctx.RequestBodyBytes())
}

func (Full) ResponseFullBody(ctx Context) any {
	return string(ctx.ResponseBodyBytes())
}
