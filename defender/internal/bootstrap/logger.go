package bootstrap

import (
	"defly-defender/internal/config"
	"defly-defender/internal/environments"
)

func NewLogger() config.Logger {
	return config.Logger{
		Format: environments.ServerLoggerFormat.Value(),
		Timezone: environments.ServerLoggerTimezone.Value(),
		File: environments.ServerLoggerFileEnable.Value(),
		Path: environments.ServerLoggerFilePath.Value(),
	}
}
