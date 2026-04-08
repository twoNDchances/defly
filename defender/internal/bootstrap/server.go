package bootstrap

import (
	"defly-defender/internal/config"
	"defly-defender/internal/environments"
)

func NewServer() {
	logger := NewLogger()
	logger.From = "SERVER"
	server := config.Server{
		Port:        environments.ServerPort.Value(),
		EnableHttps: environments.ServerHTTPSEnable.Value(),
		Certificate: environments.ServerHTTPSCert.Value(),
		Key:         environments.ServerHTTPSKey.Value(),
		Logger:      logger,
	}
	if err := server.Boot(); err != nil {
		panic(err)
	}
}
