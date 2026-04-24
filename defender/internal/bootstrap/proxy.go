package bootstrap

import (
	"defly-defender/internal/config"
	"defly-defender/internal/environments"
	"defly-defender/internal/utilities"
)

func NewProxy() {
	proxyTrustedEnable := environments.ProxyTrustedEnable.Value()
	proxyTrusted := config.Trusted{
		Enable: proxyTrustedEnable,
	}
	if proxyTrustedEnable {
		proxyTrusted.List = environments.ProxyTrustedList.Value()
	}

	proxyLoggerFileEnable := environments.ProxyLoggerFileEnable.Value()
	proxyLogger := config.Logger{
		From:     "PROXY",
		Format:   environments.ProxyLoggerFormat.Value(),
		Timezone: environments.ProxyLoggerTimezone.Value(),
		File:     proxyLoggerFileEnable,
	}
	if proxyLoggerFileEnable {
		proxyLogger.Path = environments.ProxyLoggerFilePath.Value()
	}

	proxy := config.Proxy{
		Address: config.Address{
			Port: environments.ProxyPort.Value(),
		},
		Logger: proxyLogger,
		Severity: config.Severity{
			Info:      utilities.StringToInteger(environments.ProxySeverityInfo.Value()),
			Notice:    utilities.StringToInteger(environments.ProxySeverityNotice.Value()),
			Warning:   utilities.StringToInteger(environments.ProxySeverityWarning.Value()),
			Error:     utilities.StringToInteger(environments.ProxySeverityError.Value()),
			Critical:  utilities.StringToInteger(environments.ProxySeverityCritical.Value()),
			Alert:     utilities.StringToInteger(environments.ProxySeverityAlert.Value()),
			Emergency: utilities.StringToInteger(environments.ProxySeverityEmergency.Value()),
		},
		Violation: config.Violation{
			Score: utilities.StringToInteger(environments.ProxyViolationScore.Value()),
			Level: utilities.StringToInteger(environments.ProxyViolationLevel.Value()),
		},
		BackendUrl:   environments.ProxyBackendUrl.Value().String(),
		Trusted:      proxyTrusted,
		PreserveHost: environments.ProxyPreserveHost.Value(),
	}
	if err := proxy.Boot(); err != nil {
		panic(utilities.Danger(err.Error()))
	}
}
