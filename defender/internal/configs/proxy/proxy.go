package proxy

import (
	"fmt"
	"log"
	"net/http"
	"net/http/httputil"
	"net/url"

	"defly-defender/internal/configs"
	"defly-defender/internal/globals"
	"defly-defender/internal/utilities"
	"github.com/gin-gonic/gin"
)

type Proxy struct {
	Address      configs.Address
	Absorber     configs.Absorber
	Logger       configs.Logger
	Severity     Severity
	Violation    Violation
	BackendUrl   string
	Trusted      Trusted
	PreserveHost bool
	Error        configs.Error
}

func (p Proxy) Boot() error {
	proxy := gin.New()
	p.Absorber.Recover(proxy)

	errorFile, err := p.Error.Boot()
	if err != nil {
		return fmt.Errorf("%s", p.Error.Format(err.Error()))
	}
	if errorFile != nil {
		defer errorFile.Close()
	}

	file, err := p.Logger.Boot(proxy)
	if err != nil {
		return p.Error.LogError(err)
	}
	if file != nil {
		defer file.Close()
	}

	proxy.Use(func(ctx *gin.Context) {
		globals.Gate.RLock()
		defer globals.Gate.RUnlock()
		ctx.Next()
	})

	target, err := url.Parse(p.BackendUrl)
	if err != nil {
		return p.Error.LogError(err)
	}

	reverseProxy := &httputil.ReverseProxy{
		Rewrite: func(request *httputil.ProxyRequest) {
			request.SetURL(target)
			request.SetXForwarded()
			if p.PreserveHost {
				request.Out.Host = request.In.Host
			}
		},
		ModifyResponse: func(r *http.Response) error { return nil },
		ErrorHandler: func(writer http.ResponseWriter, request *http.Request, err error) {
			writer.Header().Set("Content-Type", "application/json")
			writer.WriteHeader(http.StatusBadGateway)
			_, _ = writer.Write([]byte(`{"message":"backend unavailable"}`))
		},
	}

	if err := p.Trusted.Trust(proxy); err != nil {
		return p.Error.LogError(err)
	}

	proxy.Any("/*proxyPath", gin.WrapH(reverseProxy))
	log.Println(utilities.Infof("Defender proxy is running at http://0.0.0.0:%s", p.Address.Port))
	return p.Error.LogError(proxy.Run(fmt.Sprintf(":%s", p.Address.Port)))
}
