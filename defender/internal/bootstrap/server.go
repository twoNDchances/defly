package bootstrap

import (
	"defly-defender/internal/config"
	"defly-defender/internal/environments"
	"defly-defender/internal/utilities"
)

func NewServer() {
	serverHTTPSEnable := environments.ServerHTTPSEnable.Value()
	serverTls := config.Tls{
		Enable: serverHTTPSEnable,
	}
	if serverHTTPSEnable {
		serverTls.Certificate = environments.ServerHTTPSCert.Value()
		serverTls.Key = environments.ServerHTTPSKey.Value()
	}

	serverLoggerFileEnable := environments.ServerLoggerFileEnable.Value()
	serverLogger := config.Logger{
		From:     "SERVER",
		Format:   environments.ServerLoggerFormat.Value(),
		Timezone: environments.ServerLoggerTimezone.Value(),
		File:     serverLoggerFileEnable,
	}
	if serverLoggerFileEnable {
		serverLogger.Path = environments.ServerLoggerFilePath.Value()
	}

	server := config.Server{
		Address: config.Address{
			Port: environments.ServerPort.Value(),
		},
		Tls:    serverTls,
		Logger: serverLogger,
	}
	if err := server.Boot(); err != nil {
		panic(utilities.Danger(err.Error()))
	}
}
