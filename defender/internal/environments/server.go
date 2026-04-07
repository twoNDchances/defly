package environments

import (
	"defly-defender/internal/utilities"

	"github.com/dogmatiq/ferrite"
)

var (
	ServerPort = ferrite.NetworkPort("SERVER_PORT", "Port number for Defly Defender Server").
			WithDefault("9947").
			Required()

	ServerEnableHTTPS = ferrite.Bool("SERVER_ENABLE_HTTPS", "Enable/Disable HTTPS for Defly Defender Server").
				WithDefault(false).
				Required()

	ServerHTTPSCert = ferrite.String("SERVER_HTTPS_CERT", "Path to TLS certificate file").
			WithConstraint("Validate file exists", utilities.PathExists).
			WithDefault("tls/tls.crt").
			Required(ferrite.RelevantIf(ServerEnableHTTPS))

	ServerHTTPSKey = ferrite.String("SERVER_HTTPS_KEY", "Path to TLS key file").
			WithConstraint("Validate file exists", utilities.PathExists).
			WithDefault("tls/tls.key").
			Required(ferrite.RelevantIf(ServerEnableHTTPS))
)
