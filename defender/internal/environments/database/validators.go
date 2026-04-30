package database

import (
	"strings"
)

func validateDatabaseHost(value string) bool {
	return validateDatabaseToken(value)
}

func validateDatabaseName(value string) bool {
	return validateDatabaseToken(value)
}

func validateDatabaseUser(value string) bool {
	return validateDatabaseToken(value)
}

func validateDatabaseToken(value string) bool {
	value = strings.TrimSpace(value)
	return value != "" && !strings.ContainsAny(value, " \t\r\n")
}
