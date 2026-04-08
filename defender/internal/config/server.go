package config

import (
	"fmt"

	"github.com/gofiber/fiber/v3"
	"github.com/gofiber/fiber/v3/middleware/recover"
)

type Server struct {
	Port        string
	EnableHttps bool
	Certificate string
	Key         string
	Logger      Logger
}

func (s Server) Boot() error {
	server := fiber.New()

	server.Use(recover.New())

	file := s.Logger.Boot(server)
	if file != nil {
		defer file.Close()
	}

	address := fmt.Sprintf(":%s", s.Port)
	listenConfig := fiber.ListenConfig{
		DisableStartupMessage: true,
	}

	if s.EnableHttps {
		listenConfig.CertFile = s.Certificate
		listenConfig.CertKeyFile = s.Key
	}

	return server.Listen(address, listenConfig)
}
