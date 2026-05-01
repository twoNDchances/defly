package server

import (
	"fmt"
	"log"

	"defly-defender/internal/configs"
	"defly-defender/internal/controllers"
	"defly-defender/internal/utilities"

	"github.com/gin-gonic/gin"
)

type Server struct {
	Address    configs.Address
	Absorber   configs.Absorber
	Tls        configs.Tls
	Logger     configs.Logger
	Security   Security
	Controller Controller
	Error      configs.Error
}

func (s Server) Boot() error {
	server := gin.New()
	s.Absorber.Recover(server)

	errorFile, err := s.Error.Boot()
	if err != nil {
		return s.Error.LogError(err)
	}
	if errorFile != nil {
		defer errorFile.Close()
	}

	file, err := s.Logger.Boot(server)
	if err != nil {
		return s.Error.LogError(err)
	}
	if file != nil {
		defer file.Close()
	}

	if err := s.Security.Secure(server); err != nil {
		return s.Error.LogError(err)
	}

	s.Controller.Principle = &controllers.Principle{
		Database: s.Controller.Authorization.Database,
		Error:    s.Error,
	}
	s.Controller.Decision = &controllers.Decision{
		Database: s.Controller.Authorization.Database,
		Error:    s.Error,
	}

	s.Controller.Control(server)

	scheme := "http"
	if s.Tls.Enable {
		scheme = "https"
	}
	log.Println(utilities.Infof("Defender server is running at %s://0.0.0.0:%s", scheme, s.Address.Port))
	return s.Error.LogError(s.Tls.Listen(server, fmt.Sprintf("%s:%s", s.Address.Host, s.Address.Port)))
}
