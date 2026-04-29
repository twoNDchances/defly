package proxy

import (
	"fmt"
	"strings"

	"github.com/gin-gonic/gin"
)

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
