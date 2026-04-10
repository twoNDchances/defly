package config

import (
	"defly-defender/internal/utilities"
	"fmt"
	"log"

	"github.com/gofiber/fiber/v3"
	reverse "github.com/gofiber/fiber/v3/middleware/proxy"
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
	proxy := fiber.New()

	p.Absorber.Recover(proxy)

	file := p.Logger.Boot(proxy)
	if file != nil {
		defer file.Close()
	}

	p.Locker.Lock(proxy)

	proxy.Use(reverse.Balancer(reverse.Config{
		Servers: []string{
			p.BackendUrl,
		},
		ModifyRequest: func(c fiber.Ctx) error {
			return nil
		},
		ModifyResponse: func(c fiber.Ctx) error {
			return nil
		},
	}))

	listenConfig := fiber.ListenConfig{
		DisableStartupMessage: true,
	}

	log.Println(utilities.Infof("Defender proxy is running at http://0.0.0.0:%s", p.Address.Port))
	return proxy.Listen(fmt.Sprintf(":%s", p.Address.Port), listenConfig)
}
