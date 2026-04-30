package database

import "github.com/dogmatiq/ferrite"

var (
	DatabaseHost = ferrite.String("DATABASE_HOST", "Database host").
			WithDefault("127.0.0.1").
			WithConstraint("Must be a valid database host value without whitespace", validateDatabaseHost).
			Required()

	DatabasePort = ferrite.NetworkPort("DATABASE_PORT", "Database port").
			WithDefault("3306").
			Required()

	DatabaseName = ferrite.String("DATABASE_NAME", "Database name").
			WithDefault("defly_manager").
			WithConstraint("Must be a valid database name value without whitespace", validateDatabaseName).
			Required()

	DatabaseUser = ferrite.String("DATABASE_USER", "Database username").
			WithDefault("root").
			WithConstraint("Must be a valid database username value without whitespace", validateDatabaseUser).
			Required()

	DatabasePass = ferrite.String("DATABASE_PASS", "Database password").
			WithDefault("").
			Required()
)
