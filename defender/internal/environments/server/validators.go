package server

import (
	"fmt"
	"regexp"
	"strings"
)

var (
	serverPathPattern       = regexp.MustCompile(`^[A-Za-z0-9._~-]+(?:/[A-Za-z0-9._~-]+)*$`)
	serverHeaderNamePattern = regexp.MustCompile("^[!#$%&'*+\\-.^_`|~0-9A-Za-z]+$")
)

func validateSecurityManager(value string) bool {
	value = strings.TrimSpace(value)
	return value != "" && !strings.ContainsAny(value, " \t\r\n/\\:")
}

func validateServerPath(value string) bool {
	if !serverPathPattern.MatchString(value) {
		return false
	}
	for segment := range strings.SplitSeq(value, "/") {
		if segment == "." || segment == ".." {
			return false
		}
	}
	return true
}

func validateServerHeaderName(value string) bool {
	value = strings.TrimSpace(value)
	return value != "" && serverHeaderNamePattern.MatchString(value)
}

func ValidatePathsAndMethods() error {
	paths := map[string]string{
		"SERVER_PATH_PRINCIPLES": ServerPathPrinciples.Value(),
		"SERVER_PATH_DECISIONS":  ServerPathDecisions.Value(),
	}
	if err := validateDistinctValues("server path", paths); err != nil {
		return err
	}

	methodGroups := map[string]map[string]string{
		ServerPathPrinciples.Value(): {
			"SERVER_METHOD_APPLY":  ServerMethodApply.Value(),
			"SERVER_METHOD_REVOKE": ServerMethodRevoke.Value(),
		},
		ServerPathDecisions.Value(): {
			"SERVER_METHOD_IMPLEMENT": ServerMethodImplement.Value(),
			"SERVER_METHOD_SUSPEND":   ServerMethodSuspend.Value(),
		},
	}
	for path, methods := range methodGroups {
		if err := validateDistinctValues(fmt.Sprintf("server method for %q path", path), methods); err != nil {
			return err
		}
	}

	return nil
}

func validateDistinctValues(kind string, values map[string]string) error {
	seen := map[string]string{}
	for name, value := range values {
		key := strings.ToLower(value)
		if existingName, exists := seen[key]; exists {
			return fmt.Errorf("%s %s duplicates %s with value %q", kind, name, existingName, value)
		}
		seen[key] = name
	}
	return nil
}
