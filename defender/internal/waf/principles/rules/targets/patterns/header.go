package patterns

type Header struct{}

func (Header) RequestHeaderKeys(ctx Context) any {
	return headerKeys(ctx.RequestHeaders())
}

func (Header) ResponseHeaderKeys(ctx Context) any {
	return headerKeys(ctx.ResponseHeaders())
}

func (Header) RequestHeaderValues(ctx Context) any {
	return headerValues(ctx.RequestHeaders())
}

func (Header) ResponseHeaderValues(ctx Context) any {
	return headerValues(ctx.ResponseHeaders())
}

func (Header) RequestHeaderSize(ctx Context) any {
	return float64(len(ctx.RequestHeaders()))
}

func (Header) ResponseHeaderSize(ctx Context) any {
	return float64(len(ctx.ResponseHeaders()))
}
