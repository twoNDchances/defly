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
	"defly-defender/internal/waf"
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
		return p.Error.LogError(err)
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
		globals.Pauser.RLock()
		defer globals.Pauser.RUnlock()
		ctx.Next()
	})

	target, err := url.Parse(p.BackendUrl)
	if err != nil {
		return p.Error.LogError(err)
	}

	wafCore := waf.Factory{}.New(waf.Config{
		ViolationScore: p.Violation.Score,
		ViolationLevel: p.Violation.Level,
		Severity: map[string]int{
			"info":      p.Severity.Info,
			"notice":    p.Severity.Notice,
			"warning":   p.Severity.Warning,
			"error":     p.Severity.Error,
			"critical":  p.Severity.Critical,
			"alert":     p.Severity.Alert,
			"emergency": p.Severity.Emergency,
		},
	})

	reverseProxy := &httputil.ReverseProxy{
		Rewrite: func(request *httputil.ProxyRequest) {
			targetURL := target
			tx := wafCore.TransactionFrom(request.In)
			if tx != nil && tx.Result.RedirectURL != "" {
				if parsed, err := url.Parse(tx.Result.RedirectURL); err == nil {
					targetURL = parsed
				}
			}
			request.SetURL(targetURL)
			request.SetXForwarded()
			if p.PreserveHost {
				request.Out.Host = request.In.Host
			}
			if tx != nil {
				tx.Request = request.Out
			}
		},
		ModifyResponse: func(r *http.Response) error {
			tx := wafCore.TransactionFrom(r.Request)
			if tx == nil {
				tx = wafCore.NewBlankTransaction(r.Request)
			}
			return wafCore.RunResponse(tx, r)
		},
		ErrorHandler: func(writer http.ResponseWriter, request *http.Request, err error) {
			writer.Header().Set("Content-Type", "application/json")
			writer.WriteHeader(http.StatusBadGateway)
			_, _ = writer.Write([]byte(`{"message":"backend unavailable"}`))
		},
	}

	if err := p.Trusted.Trust(proxy); err != nil {
		return p.Error.LogError(err)
	}

	proxy.Any("/*proxyPath", func(ctx *gin.Context) {
		tx, err := wafCore.NewTransaction(ctx.Request)
		if err != nil {
			_ = p.Error.LogError(err)
			ctx.JSON(http.StatusInternalServerError, gin.H{"message": "waf request capture failed"})
			return
		}
		wafCore.RunRequest(tx)
		if wafCore.Decisions().ApplyRequest(tx, ctx.Writer) {
			ctx.Abort()
			return
		}
		wafCore.SetTransaction(ctx.Request, tx)
		reverseProxy.ServeHTTP(ctx.Writer, ctx.Request)
	})
	log.Println(utilities.Infof("Defender proxy is running at http://0.0.0.0:%s", p.Address.Port))
	return p.Error.LogError(proxy.Run(fmt.Sprintf("%s:%s", p.Address.Host, p.Address.Port)))
}
