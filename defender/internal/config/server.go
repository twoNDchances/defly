package config

import (
	"defly-defender/internal/utilities"
	"fmt"
	"log"

	"github.com/gofiber/fiber/v3"
)

type Server struct {
	Address  Address
	Absorber Absorber
	Tls      Tls
	Logger   Logger
	Locker   Locker
}

func (s Server) Boot() error {
	server := fiber.New()

	s.Absorber.Recover(server)

	file := s.Logger.Boot(server)
	if file != nil {
		defer file.Close()
	}

	s.Locker.Lock(server)

	listenConfig := fiber.ListenConfig{
		DisableStartupMessage: true,
	}

	scheme := "http"
	s.Tls.Encrypt(&listenConfig)
	if s.Tls.Enable {
		scheme = "https"
	}

	log.Println(utilities.Infof("Defender server is running at %s://0.0.0.0:%s", scheme, s.Address.Port))
	return server.Listen(fmt.Sprintf(":%s", s.Address.Port), listenConfig)
}
