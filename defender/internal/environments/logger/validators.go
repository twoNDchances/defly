package logger

import "defly-defender/internal/utilities"

func validateLoggerFilePath(value string) bool {
	return utilities.IsCreatableFilePath(value)
}
