package bootstrap

import (
	"defly-defender/internal/configs"
	configproxy "defly-defender/internal/configs/proxy"
	envcommon "defly-defender/internal/environments/common"
	envlogger "defly-defender/internal/environments/logger"
	envproxy "defly-defender/internal/environments/proxy"
)

func NewProxy() error {
	from := "PROXY"
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

	errorFileEnable := envcommon.ErrorFileEnable.Value()
	proxyError := configs.Error{
		From:       from,
		Label:      "runtime",
		FileEnable: errorFileEnable,
	}
	if errorFileEnable {
		proxyError.DirectoryPath = envcommon.ErrorDirectoryPath.Value()
	}

	proxy := configproxy.Proxy{
		Address: configs.Address{
			Port: envproxy.ProxyPort.Value(),
		},
		Logger: proxyLogger,
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
		Error:        proxyError,
	}
	if err := proxy.Boot(); err != nil {
		return err
	}
	return nil
}
