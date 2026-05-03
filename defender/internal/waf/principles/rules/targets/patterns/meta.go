package patterns

type Meta struct{}

func (Meta) RequestMetaURLPort(ctx Context) any {
	return ctx.RequestPort()
}

func (Meta) RequestMetaProtocol(ctx Context) any {
	return ctx.RequestProto()
}

func (Meta) RequestMetaIP(ctx Context) any {
	return ctx.RequestRemoteAddr()
}

func (Meta) RequestMetaMethod(ctx Context) any {
	return ctx.RequestMethod()
}

func (Meta) RequestMetaURLPath(ctx Context) any {
	return ctx.RequestPath()
}

func (Meta) RequestMetaURLScheme(ctx Context) any {
	return ctx.RequestScheme()
}

func (Meta) RequestMetaURLHost(ctx Context) any {
	return ctx.RequestHost()
}

func (Meta) ResponseMetaStatus(ctx Context) any {
	return float64(ctx.ResponseStatusCode())
}

func (Meta) ResponseMetaProtocol(ctx Context) any {
	return ctx.ResponseProto()
}
