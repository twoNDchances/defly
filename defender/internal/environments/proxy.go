package environments

import (
	"strconv"

	"github.com/dogmatiq/ferrite"
)

func validateStringRange(value string, min, max int) bool {
	integer, err := strconv.Atoi(value)
	if err != nil {
		return false
	}
	if integer < min || integer > max {
		return false
	}
	return true
}

var severityScore = func(value string) bool {
	return validateStringRange(value, 1, 1000)
}

var violationLevel = func(value string) bool {
	return validateStringRange(value, 1, 1000000)
}

var violationScore = func(value string) bool {
	return validateStringRange(value, 5, 100000)
}

var (
	ProxyPort = ferrite.NetworkPort("PROXY_PORT", "Port number for Defly Defender Proxy").
			WithDefault("9948").
			Required()

	ProxySeverityInfo = ferrite.String("PROXY_SEVERITY_INFO", "Assign a score to the severity level of the Info").
				WithConstraint("Validate in range", severityScore).
				WithDefault("1").
				Required()

	ProxySeverityNotice = ferrite.String("PROXY_SEVERITY_NOTICE", "Assign a score to the severity level of the Notice").
				WithConstraint("Validate in range", severityScore).
				WithDefault("2").
				Required()

	ProxySeverityWarning = ferrite.String("PROXY_SEVERITY_WARNING", "Assign a score to the severity level of the Warning").
				WithConstraint("Validate in range", severityScore).
				WithDefault("3").
				Required()

	ProxySeverityError = ferrite.String("PROXY_SEVERITY_ERROR", "Assign a score to the severity level of the Error").
				WithConstraint("Validate in range", severityScore).
				WithDefault("4").
				Required()

	ProxySeverityCritical = ferrite.String("PROXY_SEVERITY_CRITICAL", "Assign a score to the severity level of the Critical").
				WithConstraint("Validate in range", severityScore).
				WithDefault("5").
				Required()

	ProxySeverityAlert = ferrite.String("PROXY_SEVERITY_ALERT", "Assign a score to the severity level of the Alert").
				WithConstraint("Validate in range", severityScore).
				WithDefault("6").
				Required()

	ProxySeverityEmergency = ferrite.String("PROXY_SEVERITY_EMERGENCY", "Assign a score to the severity level of the Emergency").
				WithConstraint("Validate in range", severityScore).
				WithDefault("7").
				Required()

	ProxyViolationLevel = ferrite.String("PROXY_VIOLATION_LEVEL", "Assign a level of investigation when a violation occurs").
				WithConstraint("Validate in range", violationLevel).
				WithDefault("1").
				Required()

	ProxyViolationScore = ferrite.String("PROXY_VIOLATION_SCORE", "Assign a number of score to take action when a violation occurs").
				WithConstraint("Validate in range", violationScore).
				WithDefault("5").
				Required()

	ProxyBackendUrl = ferrite.URL("PROXY_BACKEND_URL", "Backend URL").
				WithDefault("http://localhost").
				Required()
)
