package common

import "defly-defender/internal/utilities"

func validateErrorDirectoryPath(value string) bool {
	return utilities.IsCreatableDirectoryPath(value)
}
