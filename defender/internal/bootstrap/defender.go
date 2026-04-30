package bootstrap

import (
	"defly-defender/internal/configs"
	"defly-defender/internal/environments/common"
)

func NewDefender() configs.Defender {
	return configs.Defender{
		Name: common.DefenderName.Value(),
	}
}
