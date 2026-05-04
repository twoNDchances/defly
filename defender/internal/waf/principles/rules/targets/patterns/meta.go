package patterns

type Meta struct{}

func (Meta) RequestMetaURLPort(ctx Context) any {
	return ctx.RequestPort()
}

func (Meta) RequestMetaProtocol(ctx Context) any {
	return ctx.RequestProto()
}

func (Meta) RequestMetaIP(ctx Context) any {
	value := ctx.RequestRemoteAddr()
	if value == "[::1]" {
		return "127.0.0.1"
	}
	if len(value) > len("[::1]:") && value[:len("[::1]:")] == "[::1]:" {
		return "127.0.0.1"
	}
	return value
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
