package server

import "github.com/dogmatiq/ferrite"

var (
	ServerPort = ferrite.NetworkPort("SERVER_PORT", "Port number for Defly Defender Server").
			WithDefault("9947").
			Required()

	ServerHTTPSEnable = ferrite.Bool("SERVER_HTTPS_ENABLE", "Enable/Disable HTTPS for Defly Defender Server").
				WithDefault(true).
				Required()

	ServerPathPrefix = ferrite.String("SERVER_CONTROLLER_PATH_PREFIX", "Base path prefix for Defender Server routes").
				WithDefault("api/v1").
				WithConstraint("Must be a valid relative URL path without leading/trailing slash or spaces", validateServerPath).
				Required()

	ServerPathPrinciples = ferrite.String("SERVER_CONTROLLER_PATH_PRINCIPLES", "Path segment for Defender Server principle routes").
				WithDefault("principles").
				WithConstraint("Must be a valid relative URL path without leading/trailing slash or spaces", validateServerPath).
				Required()

	ServerMethodApply = ferrite.Enum("SERVER_CONTROLLER_METHOD_APPLY", "HTTP method for applying principles").
				WithMembers("post", "put", "patch", "delete").
				WithDefault("put").
				Required()

	ServerMethodRevoke = ferrite.Enum("SERVER_CONTROLLER_METHOD_REVOKE", "HTTP method for revoking principles").
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

	ServerControllerPermissionEmail = ferrite.String("SERVER_CONTROLLER_PERMISSION_EMAIL", "HTTP header key used to identify the manager user email for permission checks").
					WithDefault("X-Executor").
					WithConstraint("Must be a valid HTTP header name", validateServerHeaderName).
					Required()

	ServerSecurityManager = ferrite.String("SERVER_SECURITY_MANAGER", "Manager IP address or host allowed to access Defender Server").
				WithDefault("manager").
				WithConstraint("Must be a valid manager IP address or host name", validateSecurityManager).
				Required()

	ServerSecurityUsername = ferrite.String("SERVER_SECURITY_USERNAME", "Username for Defender Server basic authentication").
				WithMinimumLength(4).
				WithDefault("defly-defender").
				Required()

	ServerSecurityPassword = ferrite.String("SERVER_SECURITY_PASSWORD", "Password for Defender Server basic authentication").
				WithMinimumLength(4).
				WithDefault("P@55w0rd").
				Required()
)
