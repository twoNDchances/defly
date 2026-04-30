package bootstrap

import (
	"defly-defender/internal/configs"
	"defly-defender/internal/environments/common"
)

func NewError(from, label string) configs.Error {
	errorFileEnable := common.ErrorFileEnable.Value()
	err := configs.Error{
		From:       from,
		Label:      label,
		FileEnable: errorFileEnable,
	}
	if errorFileEnable {
		err.DirectoryPath = common.ErrorDirectoryPath.Value()
	}
	return err
}
