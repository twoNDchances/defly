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
	runtimeError := NewError(from, "runtime")
	errorFile, err := runtimeError.Boot()
	if err != nil {
		return runtimeError.LogError(err)
	}
	if errorFile != nil {
		defer errorFile.Close()
	}

	serverHTTPSEnable := envserver.ServerHTTPSEnable.Value()
	serverTls := configs.Tls{
		Enable: serverHTTPSEnable,
		Name:   envcommon.DefenderName.Value(),
	}

	if err := envserver.ValidatePathsAndMethods(); err != nil {
		return runtimeError.LogError(err)
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
		Authorization: configserver.Authorization{
			Database: NewDatabase(),
			Email:    envserver.ServerControllerPermissionEmail.Value(),
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

	serverSecurity := configserver.Security{
		Manager:  envserver.ServerSecurityManager.Value(),
		Username: envserver.ServerSecurityUsername.Value(),
		Password: envserver.ServerSecurityPassword.Value(),
	}

	server := configserver.Server{
		Address: configs.Address{
			Host: "",
			Port: envserver.ServerPort.Value(),
		},
		Tls:        serverTls,
		Logger:     serverLogger,
		Security:   serverSecurity,
		Controller: serverController,
		Error:      runtimeError,
	}
	if err := server.Boot(); err != nil {
		return runtimeError.LogError(err)
	}
	return nil
}
