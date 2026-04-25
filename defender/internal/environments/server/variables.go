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

	ServerPathPrefix = ferrite.String("SERVER_CONTROLLER_PATH_PREFIX", "Base path prefix for Defender Server routes").
				WithDefault("api/v1").
				WithConstraint("Must be a valid relative URL path without leading/trailing slash or spaces", validateServerPath).
				Required()

	ServerPathState = ferrite.String("SERVER_CONTROLLER_PATH_STATE", "Path segment for Defender Server state routes").
			WithDefault("state").
			WithConstraint("Must be a valid relative URL path without leading/trailing slash or spaces", validateServerPath).
			Required()

	ServerMethodCheck = ferrite.Enum("SERVER_CONTROLLER_METHOD_CHECK", "HTTP method for checking state").
				WithMembers("get", "post", "put", "patch", "delete").
				WithDefault("get").
				Required()

	ServerMethodInspect = ferrite.Enum("SERVER_CONTROLLER_METHOD_INSPECT", "HTTP method for inspecting state").
				WithMembers("get", "post", "put", "patch", "delete").
				WithDefault("post").
				Required()

	ServerPathPolicies = ferrite.String("SERVER_CONTROLLER_PATH_POLICIES", "Path segment for Defender Server policy routes").
				WithDefault("policies").
				WithConstraint("Must be a valid relative URL path without leading/trailing slash or spaces", validateServerPath).
				Required()

	ServerMethodApply = ferrite.Enum("SERVER_CONTROLLER_METHOD_APPLY", "HTTP method for applying policies").
				WithMembers("post", "put", "patch", "delete").
				WithDefault("put").
				Required()

	ServerMethodRevoke = ferrite.Enum("SERVER_CONTROLLER_METHOD_REVOKE", "HTTP method for revoking policies").
				WithMembers("post", "put", "patch", "delete").
				WithDefault("delete").
				Required()

	ServerPathDecisions = ferrite.String("SERVER_CONTROLLER_PATH_DECISIONS", "Path segment for Defender Server decision routes").
				WithDefault("decisions").
				WithConstraint("Must be a valid relative URL path without leading/trailing slash or spaces", validateServerPath).
				Required()

	ServerMethodImplement = ferrite.Enum("SERVER_CONTROLLER_METHOD_IMPLEMENT", "HTTP method for implementing decisions").
				WithMembers("post", "put", "patch", "delete").
				WithDefault("put").
				Required()

	ServerMethodSuspend = ferrite.Enum("SERVER_CONTROLLER_METHOD_SUSPEND", "HTTP method for suspending decisions").
				WithMembers("post", "put", "patch", "delete").
				WithDefault("delete").
				Required()
)
