package config

import (
	"github.com/gin-gonic/gin"
)

type Address struct {
	Port string
}

type Absorber struct{}

func (a Absorber) Recover(application *gin.Engine) {
	application.Use(gin.Recovery())
}

type Locker struct{}

func (l Locker) Lock(application *gin.Engine) {
	application.Use(func(ctx *gin.Context) {
		ctx.Next()
	})
}

type Tls struct {
	Enable      bool
	Certificate string
	Key         string
}

func (t Tls) Listen(application *gin.Engine, address string) error {
	if t.Enable {
		return application.RunTLS(address, t.Certificate, t.Key)
	}
	return application.Run(address)
}
