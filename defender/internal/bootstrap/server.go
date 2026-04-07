package bootstrap

import (
	"defly-defender/internal/config"
	"defly-defender/internal/environments"
)

func NewServer() {
	server := config.Server{
		Port:        environments.ServerPort.Value(),
		EnableHttps: environments.ServerEnableHTTPS.Value(),
		Certificate: environments.ServerHTTPSCert.Value(),
		Key:         environments.ServerHTTPSKey.Value(),
	}
	server.Boot()
}
