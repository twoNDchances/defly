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
	Storage    Storage
	Error      configs.Error
}

func (s Server) Boot() error {
	server := gin.New()
	s.Absorber.Recover(server)

	errorFile, err := s.Error.Boot()
	if err != nil {
		return fmt.Errorf("%s", s.Error.Format(err.Error()))
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

	storage := &s.Storage
	if err := storage.Load(); err != nil {
		return s.Error.LogError(err)
	}
	s.Controller.Policy = &controllers.Policy{}
	s.Controller.Decision = &controllers.Decision{}

	s.Controller.Control(server)

	scheme := "http"
	if s.Tls.Enable {
		scheme = "https"
	}
	log.Println(utilities.Infof("Defender server is running at %s://0.0.0.0:%s", scheme, s.Address.Port))
	return s.Error.LogError(s.Tls.Listen(server, fmt.Sprintf(":%s", s.Address.Port)))
}
