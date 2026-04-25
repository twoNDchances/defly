package server

import "defly-defender/internal/utilities"

func validateTLSFilePath(value string) bool {
	return utilities.IsCreatableFilePath(value)
}
