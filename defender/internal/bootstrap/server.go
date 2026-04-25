package bootstrap

import (
	"defly-defender/internal/config"
	envcommon "defly-defender/internal/environments/common"
	envlogger "defly-defender/internal/environments/logger"
	envserver "defly-defender/internal/environments/server"
)

func NewServer() error {
	from := "SERVER"
	serverHTTPSEnable := envserver.ServerHTTPSEnable.Value()
	serverTls := config.Tls{
		Enable: serverHTTPSEnable,
	}
	if serverHTTPSEnable {
		serverTls.Certificate = envserver.ServerHTTPSCert.Value()
		serverTls.Key = envserver.ServerHTTPSKey.Value()
	}

	if err := envserver.ValidatePathsAndMethods(); err != nil {
		return err
	}

	serverController := config.Controller{
		Path: config.Path{
			Prefix:    envserver.ServerPathPrefix.Value(),
			State:     envserver.ServerPathState.Value(),
			Policies:  envserver.ServerPathPolicies.Value(),
			Decisions: envserver.ServerPathDecisions.Value(),
		},
		Method: config.Method{
			Check:     envserver.ServerMethodCheck.Value(),
			Inspect:   envserver.ServerMethodInspect.Value(),
			Apply:     envserver.ServerMethodApply.Value(),
			Revoke:    envserver.ServerMethodRevoke.Value(),
			Implement: envserver.ServerMethodImplement.Value(),
			Suspend:   envserver.ServerMethodSuspend.Value(),
		},
	}

	serverLoggerFileEnable := envlogger.ServerLoggerFileEnable.Value()
	serverLogger := config.Logger{
		From:     from,
		Format:   envlogger.ServerLoggerFormat.Value(),
		Timezone: envlogger.ServerLoggerTimezone.Value(),
		File:     serverLoggerFileEnable,
	}
	if serverLoggerFileEnable {
		serverLogger.Path = envlogger.ServerLoggerFilePath.Value()
	}

	errorFileEnable := envcommon.ErrorFileEnable.Value()
	serverError := config.Error{
		From:       from,
		Label:      "runtime",
		FileEnable: errorFileEnable,
	}
	if errorFileEnable {
		serverError.DirectoryPath = envcommon.ErrorDirectoryPath.Value()
	}

	server := config.Server{
		Address: config.Address{
			Port: envserver.ServerPort.Value(),
		},
		Tls:        serverTls,
		Logger:     serverLogger,
		Controller: serverController,
		Error:      serverError,
	}
	if err := server.Boot(); err != nil {
		return err
	}
	return nil
}
