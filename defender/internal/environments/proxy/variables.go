package proxy

import "github.com/dogmatiq/ferrite"

var (
	ProxyPort = ferrite.NetworkPort("PROXY_PORT", "Port number for Defly Defender Proxy").
			WithDefault("9948").
			Required()

	ProxyTrustedEnable = ferrite.Bool("PROXY_TRUSTED_ENABLE", "Enable/Disable trusted proxies").
				WithDefault(false).
				Required()

	ProxyTrustedList = ferrite.String("PROXY_TRUSTED_LIST", "Comma-separated list of trusted proxy IPs or CIDRs").
				WithExample("127.0.0.1,10.0.0.0/8,192.168.1.0/24", "local proxy and private networks").
				WithConstraint("Must be a comma-separated list of valid IP addresses or CIDR blocks", validateTrustedProxyList).
				Required(ferrite.RelevantIf(ProxyTrustedEnable))

	ProxyPreserveHost = ferrite.Bool("PROXY_PRESERVE_HOST", "Preserve original Host header when forwarding request to backend").
				WithDefault(true).
				Required()

	ProxySeverityInfo = ferrite.Signed[int]("PROXY_SEVERITY_INFO", "Assign a score to the severity level of the Info").
				WithMinimum(1).
				WithMaximum(1000).
				WithDefault(1).
				Required()

	ProxySeverityNotice = ferrite.Signed[int]("PROXY_SEVERITY_NOTICE", "Assign a score to the severity level of the Notice").
				WithMinimum(1).
				WithMaximum(1000).
				WithDefault(2).
				Required()

	ProxySeverityWarning = ferrite.Signed[int]("PROXY_SEVERITY_WARNING", "Assign a score to the severity level of the Warning").
				WithMinimum(1).
				WithMaximum(1000).
				WithDefault(3).
				Required()

	ProxySeverityError = ferrite.Signed[int]("PROXY_SEVERITY_ERROR", "Assign a score to the severity level of the Error").
				WithMinimum(1).
				WithMaximum(1000).
				WithDefault(4).
				Required()

	ProxySeverityCritical = ferrite.Signed[int]("PROXY_SEVERITY_CRITICAL", "Assign a score to the severity level of the Critical").
				WithMinimum(1).
				WithMaximum(1000).
				WithDefault(5).
				Required()

	ProxySeverityAlert = ferrite.Signed[int]("PROXY_SEVERITY_ALERT", "Assign a score to the severity level of the Alert").
				WithMinimum(1).
				WithMaximum(1000).
				WithDefault(6).
				Required()

	ProxySeverityEmergency = ferrite.Signed[int]("PROXY_SEVERITY_EMERGENCY", "Assign a score to the severity level of the Emergency").
				WithMinimum(1).
				WithMaximum(1000).
				WithDefault(7).
				Required()

	ProxyViolationLevel = ferrite.Signed[int]("PROXY_VIOLATION_LEVEL", "Assign a level of investigation when a violation occurs").
				WithMinimum(1).
				WithMaximum(1000000).
				WithDefault(1).
				Required()

	ProxyViolationScore = ferrite.Signed[int]("PROXY_VIOLATION_SCORE", "Assign a number of score to take action when a violation occurs").
				WithMinimum(5).
				WithMaximum(100000).
				WithDefault(5).
				Required()

	ProxyBackendUrl = ferrite.URL("PROXY_BACKEND_URL", "Backend URL").
			WithDefault("http://localhost").
			Required()
)
