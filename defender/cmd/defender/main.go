package main

import (
	"defly-defender/internal/bootstrap"

	"github.com/dogmatiq/ferrite"
)

func main() {
	ferrite.Init()
	bootstrap.NewBanner()
	bootstrap.NewServer()
}
