package environments

import (
	"defly-defender/internal/utilities"

	"github.com/dogmatiq/ferrite"
)

var (
	ServerLoggerFormat = ferrite.String("SERVER_LOGGER_FORMAT", "Logging format for Defly Defender Server with custom %tag% template compatible with Fiber logger tags. References: https://docs.gofiber.io/middleware/logger/").
				WithDefault("[%time%] {%from%}: %status% %ip% %method% %path% %bytesSent% %bytesReceived% %error%").
				Required()

	ServerLoggerTimezone = ferrite.String("SERVER_LOGGER_TIMEZONE", "Timezone of Defly Defender Server displayed in logging").
				WithDefault("Asia/Ho_Chi_Minh").
				Required()

	ServerLoggerFileEnable = ferrite.Bool("SERVER_LOGGER_FILE_ENABLE", "Enable/Disable write logs of Defly Defender Server to file").
				WithDefault(false).
				Required()

	ServerLoggerFilePath = ferrite.String("SERVER_LOGGER_FILE_PATH", "A path where logs of Defly Defender Server stored").
				WithDefault("resources/logs/server.log").
				WithConstraint("Auto create log file, continue if success", func(s string) bool {
			_, err := utilities.CreateFileIfNotExists(s)
			return err == nil
		}).
		Required(ferrite.RelevantIf(ServerLoggerFileEnable))

	ProxyLoggerFormat = ferrite.String("PROXY_LOGGER_FORMAT", "Logging format for Defly Defender Proxy with custom %tag% template compatible with Fiber logger tags. References: https://docs.gofiber.io/middleware/logger/").
				WithDefault("[%time%] {%from%}: %status% %ip% %method% %path% %bytesSent% %bytesReceived% %error%").
				Required()

	ProxyLoggerTimezone = ferrite.String("PROXY_LOGGER_TIMEZONE", "Timezone of Defly Defender Proxy displayed in logging").
				WithDefault("Asia/Ho_Chi_Minh").
				Required()

	ProxyLoggerFileEnable = ferrite.Bool("PROXY_LOGGER_FILE_ENABLE", "Enable/Disable write logs of Defly Defender Proxy to file").
				WithDefault(false).
				Required()

	ProxyLoggerFilePath = ferrite.String("PROXY_LOGGER_FILE_PATH", "A path where logs of Defly Defender Proxy stored").
				WithDefault("resources/logs/proxy.log").
				WithConstraint("Auto create log file, continue if success", func(s string) bool {
			_, err := utilities.CreateFileIfNotExists(s)
			return err == nil
		}).
		Required(ferrite.RelevantIf(ProxyLoggerFileEnable))
)
