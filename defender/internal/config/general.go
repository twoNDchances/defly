package config

import (
	"defly-defender/internal/globals"

	"github.com/gofiber/fiber/v3"
	"github.com/gofiber/fiber/v3/middleware/recover"
)

type Address struct {
	Port string
}

type Absorber struct{}

func (a Absorber) Recover(application *fiber.App) {
	application.Use(recover.New())
}

type Locker struct{}

func (l Locker) Lock(application *fiber.App) {
	application.Use(func(c fiber.Ctx) error {
		globals.Pauser.RLock()
		defer globals.Pauser.RUnlock()
		return c.Next()
	})
}

type Tls struct {
	Enable      bool
	Certificate string
	Key         string
}

func (t Tls) Encrypt(listenConfig *fiber.ListenConfig) {
	if t.Enable {
		listenConfig.CertFile = t.Certificate
		listenConfig.CertKeyFile = t.Key
	}
}
