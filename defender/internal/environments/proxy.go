package environments

import (
	"strconv"

	"github.com/dogmatiq/ferrite"
)

var (
	ProxyPort = ferrite.NetworkPort("PROXY_PORT", "Port number for Defly Defender Proxy").
			WithDefault("9948").
			Required()

	ProxySeverityNotice = ferrite.Enum("PROXY_SEVERITY_NOTICE", "Assign a score to the severity level of the Notice").
				WithMembers(generateRangeStrings(0, 1_000_000_000)...).
				WithDefault("2").
				Required()

	ProxySeverityWarning = ferrite.Enum("PROXY_SEVERITY_WARNING", "Assign a score to the severity level of the Warning").
				WithMembers(generateRangeStrings(0, 1_000_000_000)...).
				WithDefault("3").
				Required()

	ProxySeverityError = ferrite.Enum("PROXY_SEVERITY_ERROR", "Assign a score to the severity level of the Error").
				WithMembers(generateRangeStrings(0, 1_000_000_000)...).
				WithDefault("4").
				Required()

	ProxySeverityCritical = ferrite.Enum("PROXY_SEVERITY_CRITICAL", "Assign a score to the severity level of the Critical").
				WithMembers(generateRangeStrings(0, 1_000_000_000)...).
				WithDefault("5").
				Required()

	ProxySeverityAlert = ferrite.Enum("PROXY_SEVERITY_ALERT", "Assign a score to the severity level of the Alert").
				WithMembers(generateRangeStrings(0, 1_000_000_000)...).
				WithDefault("6").
				Required()

	ProxySeverityEmergency = ferrite.Enum("PROXY_SEVERITY_EMERGENCY", "Assign a score to the severity level of the Emergency").
				WithMembers(generateRangeStrings(0, 1_000_000_000)...).
				WithDefault("7").
				Required()

	ProxyViolationLevel = ferrite.Enum("PROXY_VIOLATION_LEVEL", "Assign a level of investigation when a violation occurs").
				WithMembers(generateRangeStrings(0, 1_000_000_000_000)...).
				WithDefault("0").
				Required()

	ProxyViolationScore = ferrite.Enum("PROXY_VIOLATION_SCORE", "Assign a number of score to take action when a violation occurs").
				WithMembers(generateRangeStrings(5, 1_000_000_000_000_000)...).
				WithDefault("5").
				Required()

	ProxyBackendUrls = ferrite.URL("PROXY_BACKEND_URLS", "Backend URL").
				WithDefault("http://localhost").
				Required()
)

func generateRangeStrings(min, max int) []string {
	if min > max {
		return []string{}
	}
	result := make([]string, 0, max-min+1)
	for i := min; i <= max; i++ {
		result = append(result, strconv.Itoa(i))
	}
	return result
}
