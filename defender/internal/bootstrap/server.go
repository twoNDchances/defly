package bootstrap

import (
	"defly-defender/internal/config"
	"defly-defender/internal/environments"
	"defly-defender/internal/utilities"
)

func NewServer() {
	logger := NewLogger()
	logger.From = "SERVER"
	server := config.Server{
		Address: config.Address{
			Port: environments.ServerPort.Value(),
		},
		Tls: config.Tls{
			Enable:      environments.ServerHTTPSEnable.Value(),
			Certificate: environments.ServerHTTPSCert.Value(),
			Key:         environments.ServerHTTPSKey.Value(),
		},
		Logger: logger,
	}
	if err := server.Boot(); err != nil {
		panic(utilities.Danger(err.Error()))
	}
}
