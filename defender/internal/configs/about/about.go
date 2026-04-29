package about

import (
	"defly-defender/internal/utilities"
	"fmt"

	"github.com/common-nighthawk/go-figure"
)

type About struct {
	Author  string `mapstructure:"author"`
	Name    `mapstructure:"name"`
	Version `mapstructure:"version"`
}

func (a About) PrintBanner() {
	figure.NewColorFigure(a.Name.Project, "larry3d", "blue", true).Print()
	figure.NewColorFigure(a.Name.Application, "slant", "purple", true).Print()
}

func (a About) PrintDetail() {
	fmt.Println(utilities.Successf(`
Version: %s
Go     : %s
Gin    : %s
Author : %s
`, a.Version.Application, a.Version.Go, a.Version.Gin, a.Author))
}
