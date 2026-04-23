package config

import (
	"defly-defender/internal/utilities"
	"fmt"
	"log"
	"net/http"
	"net/http/httputil"
	"net/url"
	"strings"

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

type Trusted struct {
	Enable bool
	List   string
}

func (t *Trusted) Trust(proxy *gin.Engine) error {
	if !t.Enable {
		return proxy.SetTrustedProxies(nil)
	}

	proxies := t.Parse()
	if len(proxies) == 0 {
		return fmt.Errorf("trusted proxies are enabled but PROXY_TRUSTED_LIST is empty")
	}

	return proxy.SetTrustedProxies(proxies)
}

func (t *Trusted) Parse() []string {
	list := strings.TrimSpace(t.List)
	if list == "" {
		return nil
	}

	items := strings.Split(list, ",")
	out := make([]string, 0, len(items))
	for _, item := range items {
		item = strings.TrimSpace(item)
		if item != "" {
			out = append(out, item)
		}
	}

	if len(out) == 0 {
		return nil
	}

	return out
}

type Proxy struct {
	Address      Address
	Absorber     Absorber
	Logger       Logger
	Locker       Locker
	Severity     Severity
	Violation    Violation
	BackendUrl   string
	Trusted      Trusted
	PreserveHost bool
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
			if p.PreserveHost {
				request.Out.Host = request.In.Host
			}
		},
		ModifyResponse: func(r *http.Response) error {
			return nil
		},
		ErrorHandler: func(writer http.ResponseWriter, request *http.Request, err error) {
			writer.Header().Set("Content-Type", "application/json")
			writer.WriteHeader(http.StatusBadGateway)
			_, _ = writer.Write([]byte(`{"message":"backend unavailable"}`))
		},
	}

	if err := p.Trusted.Trust(proxy); err != nil {
		return err
	}

	proxy.Any("/*proxyPath", gin.WrapH(reverseProxy))

	log.Println(utilities.Infof("Defender proxy is running at http://0.0.0.0:%s", p.Address.Port))
	return proxy.Run(fmt.Sprintf(":%s", p.Address.Port))
}
