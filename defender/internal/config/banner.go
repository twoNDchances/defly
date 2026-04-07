package config

import (
	"defly-defender/internal/utilities"
	"fmt"

	"github.com/common-nighthawk/go-figure"
)

type Banner struct {
	ProjectName  string
	AppName      string
	AppVersion   string
	GoVersion    string
	FiberVersion string
	Author       string
}

func (b Banner) Print() {
	figure.NewColorFigure(b.ProjectName, "larry3d", "blue", true).Print()
	figure.NewColorFigure(b.AppName, "slant", "purple", true).Print()
	fmt.Println(utilities.Successf(`
Version: %s
Go     : %s
Fiber  : %s
Author : %s
`, b.AppVersion, b.GoVersion, b.FiberVersion, b.Author))
}

const (
	projectName  = "Defly"
	appName      = "Defender"
	appVersion   = "1.0.0"
	goVersion    = "1.26.1"
	fiberVersion = "3.1.0"
	author       = "https://github.com/twoNDchances"
)
