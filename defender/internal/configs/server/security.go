package server

import (
	"net"
	"strings"

	"github.com/gin-gonic/gin"
)

type Security struct {
	Manager  string
	Username string
	Password string
}

func (s Security) Secure(server *gin.Engine) error {
	managerIPs := map[string]bool{}
	if ip := net.ParseIP(s.Manager); ip != nil {
		managerIPs[ip.String()] = true
	} else {
		addresses, err := net.LookupHost(s.Manager)
		if err == nil {
			for _, address := range addresses {
				managerIPs[address] = true
			}
		}
	}

	server.Use(func(ctx *gin.Context) {
		clientIP := ctx.ClientIP()
		host := ctx.Request.Host
		hostWithoutPort, _, _ := strings.Cut(host, ":")

		if clientIP != s.Manager && !managerIPs[clientIP] && !strings.EqualFold(host, s.Manager) && !strings.EqualFold(hostWithoutPort, s.Manager) {
			ctx.AbortWithStatusJSON(403, gin.H{"error": "manager is not allowed"})
			return
		}
		ctx.Next()
	})

	server.Use(gin.BasicAuthForRealm(gin.Accounts{s.Username: s.Password}, "Defly Defender"))
	return nil
}
