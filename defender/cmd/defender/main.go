package main

import (
	"defly-defender/internal/bootstrap"
	"defly-defender/internal/utilities"
	"log"

	"github.com/dogmatiq/ferrite"
)

func main() {
	log.Println(utilities.Info("Validating..."))
	ferrite.Init()
	bootstrap.NewAbout()
	bootstrap.NewServer()
}
