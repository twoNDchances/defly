package bootstrap

import (
	"defly-defender/internal/config"
	"defly-defender/internal/environments"
	"defly-defender/internal/utilities"
)

func NewServer() {
	server := config.Server{
		Address: config.Address{
			Port: environments.ServerPort.Value(),
		},
		Tls: config.Tls{
			Enable:      environments.ServerHTTPSEnable.Value(),
			Certificate: environments.ServerHTTPSCert.Value(),
			Key:         environments.ServerHTTPSKey.Value(),
		},
		Logger: config.Logger{
			From:     "SERVER",
			Format:   environments.ServerLoggerFormat.Value(),
			Timezone: environments.ServerLoggerTimezone.Value(),
			File:     environments.ServerLoggerFileEnable.Value(),
			Path:     environments.ServerLoggerFilePath.Value(),
		},
	}
	if err := server.Boot(); err != nil {
		panic(utilities.Danger(err.Error()))
	}
}
