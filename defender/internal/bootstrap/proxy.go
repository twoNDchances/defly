package bootstrap

import (
	"defly-defender/internal/configs"
	configproxy "defly-defender/internal/configs/proxy"
	envlogger "defly-defender/internal/environments/logger"
	envproxy "defly-defender/internal/environments/proxy"
)

func NewProxy() error {
	from := "PROXY"
	runtimeError := NewError(from, "runtime")
	errorFile, err := runtimeError.Boot()
	if err != nil {
		return runtimeError.LogError(err)
	}
	if errorFile != nil {
		defer errorFile.Close()
	}

	proxyTrustedEnable := envproxy.ProxyTrustedEnable.Value()
	proxyTrusted := configproxy.Trusted{
		Enable: proxyTrustedEnable,
	}
	if proxyTrustedEnable {
		proxyTrusted.List = envproxy.ProxyTrustedList.Value()
	}

	proxyLoggerFileEnable := envlogger.ProxyLoggerFileEnable.Value()
	proxyLogger := configs.Logger{
		From:     from,
		Format:   envlogger.ProxyLoggerFormat.Value(),
		Timezone: envlogger.ProxyLoggerTimezone.Value(),
		File:     proxyLoggerFileEnable,
	}
	if proxyLoggerFileEnable {
		proxyLogger.Path = envlogger.ProxyLoggerFilePath.Value()
	}

	proxy := configproxy.Proxy{
		Address: configs.Address{
			Host: "",
			Port: envproxy.ProxyPort.Value(),
		},
		Database: NewDatabase(),
		Logger:   proxyLogger,
		Severity: configproxy.Severity{
			Info:      envproxy.ProxySeverityInfo.Value(),
			Notice:    envproxy.ProxySeverityNotice.Value(),
			Warning:   envproxy.ProxySeverityWarning.Value(),
			Error:     envproxy.ProxySeverityError.Value(),
			Critical:  envproxy.ProxySeverityCritical.Value(),
			Alert:     envproxy.ProxySeverityAlert.Value(),
			Emergency: envproxy.ProxySeverityEmergency.Value(),
		},
		Violation: configproxy.Violation{
			Score: envproxy.ProxyViolationScore.Value(),
			Level: envproxy.ProxyViolationLevel.Value(),
		},
		BackendUrl:   envproxy.ProxyBackendUrl.Value().String(),
		Trusted:      proxyTrusted,
		PreserveHost: envproxy.ProxyPreserveHost.Value(),
		Error:        runtimeError,
	}
	if err := proxy.Boot(); err != nil {
		return runtimeError.LogError(err)
	}
	return nil
}
