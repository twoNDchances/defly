package config

import (
	"defly-defender/internal/utilities"
	"fmt"
	"log"
	"net/http"
	"net/http/httputil"
	"net/url"

	"github.com/gin-gonic/gin"
)

type Severity struct {
	Info      int
	Notice    int
	Warning   int
	Error     int
	Critical  int
	Alert     int
	Emergency int
}

type Violation struct {
	Score int
	Level int
}

type Proxy struct {
	Address    Address
	Absorber   Absorber
	Logger     Logger
	Locker     Locker
	Severity   Severity
	Violation  Violation
	BackendUrl string
}

func (p Proxy) Boot() error {
	proxy := gin.New()

	p.Absorber.Recover(proxy)

	file := p.Logger.Boot(proxy)
	if file != nil {
		defer file.Close()
	}

	p.Locker.Lock(proxy)

	target, err := url.Parse(p.BackendUrl)
	if err != nil {
		return err
	}

	reverseProxy := &httputil.ReverseProxy{
		Rewrite: func(request *httputil.ProxyRequest) {
			request.SetURL(target)
			request.SetXForwarded()
		},
		ErrorHandler: func(writer http.ResponseWriter, request *http.Request, err error) {
			http.Error(writer, err.Error(), http.StatusBadGateway)
		},
	}

	proxyHandler := func(ctx *gin.Context) {
		reverseProxy.ServeHTTP(ctx.Writer, ctx.Request)
		ctx.Abort()
	}
	proxy.Any("/*proxyPath", proxyHandler)

	log.Println(utilities.Infof("Defender proxy is running at http://0.0.0.0:%s", p.Address.Port))
	return proxy.Run(fmt.Sprintf(":%s", p.Address.Port))
}
