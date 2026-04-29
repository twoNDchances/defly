package bootstrap

import (
	"defly-defender/internal/configs"
	configserver "defly-defender/internal/configs/server"
	envcommon "defly-defender/internal/environments/common"
	envlogger "defly-defender/internal/environments/logger"
	envserver "defly-defender/internal/environments/server"
)

func NewServer() error {
	from := "SERVER"
	serverHTTPSEnable := envserver.ServerHTTPSEnable.Value()
	serverTls := configs.Tls{
		Enable: serverHTTPSEnable,
		Name:   envcommon.DefenderName.Value(),
	}

	if err := envserver.ValidatePathsAndMethods(); err != nil {
		return err
	}

	serverController := configserver.Controller{
		Path: configserver.Path{
			Prefix:     envserver.ServerPathPrefix.Value(),
			Principles: envserver.ServerPathPrinciples.Value(),
			Decisions:  envserver.ServerPathDecisions.Value(),
		},
		Method: configserver.Method{
			Apply:     envserver.ServerMethodApply.Value(),
			Revoke:    envserver.ServerMethodRevoke.Value(),
			Implement: envserver.ServerMethodImplement.Value(),
			Suspend:   envserver.ServerMethodSuspend.Value(),
		},
	}

	serverLoggerFileEnable := envlogger.ServerLoggerFileEnable.Value()
	serverLogger := configs.Logger{
		From:     from,
		Format:   envlogger.ServerLoggerFormat.Value(),
		Timezone: envlogger.ServerLoggerTimezone.Value(),
		File:     serverLoggerFileEnable,
	}
	if serverLoggerFileEnable {
		serverLogger.Path = envlogger.ServerLoggerFilePath.Value()
	}

	errorFileEnable := envcommon.ErrorFileEnable.Value()
	serverError := configs.Error{
		From:       from,
		Label:      "runtime",
		FileEnable: errorFileEnable,
	}
	if errorFileEnable {
		serverError.DirectoryPath = envcommon.ErrorDirectoryPath.Value()
	}

	serverStorageType := envserver.ServerStorageType.Value()
	serverStorage := configserver.Storage{
		Type: serverStorageType,
	}
	if serverStorageType == "file" {
		serverStorage.Path = envserver.ServerStoragePath.Value()
	}

	serverSecurity := configserver.Security{
		Manager:  envserver.ServerSecurityManager.Value(),
		Username: envserver.ServerSecurityUsername.Value(),
		Password: envserver.ServerSecurityPassword.Value(),
	}

	server := configserver.Server{
		Address: configs.Address{
			Port: envserver.ServerPort.Value(),
		},
		Tls:        serverTls,
		Logger:     serverLogger,
		Security:   serverSecurity,
		Controller: serverController,
		Storage:    serverStorage,
		Error:      serverError,
	}
	if err := server.Boot(); err != nil {
		return err
	}
	return nil
}
