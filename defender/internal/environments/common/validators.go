package common

import (
	"defly-defender/internal/utilities"
	"regexp"
	"strings"
)

var defenderNamePattern = regexp.MustCompile(`^[A-Za-z0-9_.-]+$`)

func validateDefenderName(value string) bool {
	value = strings.TrimSpace(value)
	return value != "" && value != "." && value != ".." && defenderNamePattern.MatchString(value)
}

func validateErrorDirectoryPath(value string) bool {
	return utilities.IsCreatableDirectoryPath(value)
}
