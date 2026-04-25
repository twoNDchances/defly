package server

import "github.com/dogmatiq/ferrite"

var (
	ServerPort = ferrite.NetworkPort("SERVER_PORT", "Port number for Defly Defender Server").
			WithDefault("9947").
			Required()

	ServerHTTPSEnable = ferrite.Bool("SERVER_HTTPS_ENABLE", "Enable/Disable HTTPS for Defly Defender Server").
				WithDefault(true).
				Required()

	ServerHTTPSCert = ferrite.String("SERVER_HTTPS_CERT", "Path to TLS certificate file").
			WithDefault("resources/tls/tls.crt").
			WithConstraint("Must be a valid TLS certificate file path", validateTLSFilePath).
			Required(ferrite.RelevantIf(ServerHTTPSEnable))

	ServerHTTPSKey = ferrite.String("SERVER_HTTPS_KEY", "Path to TLS key file").
			WithDefault("resources/tls/tls.key").
			WithConstraint("Must be a valid TLS key file path", validateTLSFilePath).
			Required(ferrite.RelevantIf(ServerHTTPSEnable))
)
