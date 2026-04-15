package config

import (
	"defly-defender/internal/utilities"
	"fmt"
	"log"

	"github.com/gin-gonic/gin"
)

type Server struct {
	Address  Address
	Absorber Absorber
	Tls      Tls
	Logger   Logger
	Locker   Locker
}

func (s Server) Boot() error {
	server := gin.New()

	s.Absorber.Recover(server)

	file := s.Logger.Boot(server)
	if file != nil {
		defer file.Close()
	}

	s.Locker.Lock(server)

	scheme := "http"
	if s.Tls.Enable {
		scheme = "https"
	}

	log.Println(utilities.Infof("Defender server is running at %s://0.0.0.0:%s", scheme, s.Address.Port))
	return s.Tls.Listen(server, fmt.Sprintf(":%s", s.Address.Port))
}
