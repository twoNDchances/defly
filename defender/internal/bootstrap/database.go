package bootstrap

import (
	"defly-defender/internal/configs"
	envdb "defly-defender/internal/environments/database"
)

func NewDatabase() configs.Database {
	return configs.Database{
		Defender: NewDefender(),
		Address: configs.Address{
			Host: envdb.DatabaseHost.Value(),
			Port: envdb.DatabasePort.Value(),
		},
		Name:    envdb.DatabaseName.Value(),
		User:    envdb.DatabaseUser.Value(),
		Pass:    envdb.DatabasePass.Value(),
		Error:   NewError("DATABASE", "runtime"),
	}
}
