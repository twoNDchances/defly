package bootstrap

import (
	"defly-defender/internal/config"
	"defly-defender/internal/environments"
)

const (
	projectName  = "Defly"
	appName      = "Defender"
	appVersion   = "1.0.0"
	goVersion    = "1.26.1"
	fiberVersion = "3.1.0"
	author       = "https://github.com/twoNDchances"
)

func NewBanner() {
	if !environments.BannerEnable.Value() {
		return
	}
	banner := config.Banner{
		ProjectName:  projectName,
		AppName:      appName,
		AppVersion:   appVersion,
		GoVersion:    goVersion,
		FiberVersion: fiberVersion,
		Author:       author,
	}
	banner.Print()
}
