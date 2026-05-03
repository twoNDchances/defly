package patterns

import (
	"net/http"
	"net/url"
)

type Pattern interface {
	Extract(ctx Context) any
}

type pattern struct {
	extract func(ctx Context) any
}

func (p pattern) Extract(ctx Context) any {
	return p.extract(ctx)
}

type Context interface {
	RawRequest() []byte
	RawResponse() []byte
	RequestBodyBytes() []byte
	ResponseBodyBytes() []byte
	RequestHeaders() http.Header
	ResponseHeaders() http.Header
	RequestQuery() url.Values
	RequestMethod() string
	RequestProto() string
	RequestRemoteAddr() string
	RequestPath() string
	RequestScheme() string
	RequestHost() string
	RequestPort() float64
	RequestContentType() string
	ResponseStatusCode() int
	ResponseProto() string
	ResponseContentType() string
}

type Noop struct{}

func (Noop) Extract(ctx Context) any {
	return nil
}
