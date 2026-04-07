package config

import (
	"fmt"

	"github.com/gofiber/fiber/v3"
)

type Server struct {
	Port        string
	EnableHttps bool
	Certificate string
	Key         string
}

func (s Server) Boot() {
	app := fiber.New()
	var err error
	address := fmt.Sprintf(":%s", s.Port)
	if s.EnableHttps {
		err = app.Listen(address, fiber.ListenConfig{
			CertFile:    s.Certificate,
			CertKeyFile: s.Key,
		})
	} else {
		err = app.Listen(address)
	}
	if err != nil {
		panic(err)
	}
}
