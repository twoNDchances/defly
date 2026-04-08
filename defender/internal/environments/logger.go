package environments

import (
	"defly-defender/internal/utilities"

	"github.com/dogmatiq/ferrite"
)

var (
	ServerLoggerFormat = ferrite.String("SERVER_LOGGER_FORMAT", "Logging format for Defly Defender Server. References: https://docs.gofiber.io/middleware/logger").
				WithDefault("[${time}] {${from}}: ${status} ${ip} ${method} ${path} ${bytesSent} ${bytesReceived} ${error}\n").
				Required()

	ServerLoggerTimezone = ferrite.String("SERVER_LOGGER_TIMEZONE", "Timezone displayed in logging. References: https://docs.sentinel.thalesgroup.com/softwareandservices/ems/EMSdocs/WSG/Content/TimeZone.htm").
				WithDefault("Asia/Ho_Chi_Minh").
				Required()

	ServerLoggerFileEnable = ferrite.Bool("SERVER_LOGGER_FILE_ENABLE", "Enable/Disable write logs to file").
				WithDefault(true).
				Required()

	ServerLoggerFilePath = ferrite.String("SERVER_LOGGER_FILE_PATH", "A path where logs stored").
				WithDefault("resources/logs/server.log").
				WithConstraint("Auto create log file, continue if success", func(s string) bool {
			_, err := utilities.CreateFileIfNotExists(s)
			return err == nil
		}).
		Required(ferrite.RelevantIf(ServerLoggerFileEnable))
)
