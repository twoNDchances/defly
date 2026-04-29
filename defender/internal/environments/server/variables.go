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

	ServerPathGate = ferrite.String("SERVER_CONTROLLER_PATH_GATE", "Path segment for Defender proxy gate routes").
			WithDefault("gate").
			WithConstraint("Must be a valid relative URL path without leading/trailing slash or spaces", validateServerPath).
			Required()

	ServerMethodLock = ferrite.Enum("SERVER_CONTROLLER_METHOD_LOCK", "HTTP method for locking Defender proxy gate").
				WithMembers("post", "put", "patch", "delete").
				WithDefault("put").
				Required()

	ServerMethodUnlock = ferrite.Enum("SERVER_CONTROLLER_METHOD_UNLOCK", "HTTP method for unlocking Defender proxy gate").
				WithMembers("post", "put", "patch", "delete").
				WithDefault("delete").
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

	ServerStorageType = ferrite.Enum("SERVER_STORAGE_TYPE", "Storage backend used by Defender Server").
				WithMembers("file", "memory").
				WithDefault("file").
				Required()

	ServerStoragePath = ferrite.String("SERVER_STORAGE_PATH", "Path to Defender Server storage file").
				WithDefault("storage/data/data.yaml").
				WithConstraint("Must be a valid writable storage file path", validateStorageFilePath).
				Required(ferrite.RelevantWhen(ServerStorageType, "file"))

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
