package main

import (
	"context"
	"defly-defender/internal/bootstrap"
	"defly-defender/internal/utilities"
	"log"
	"os"
	"os/signal"
	"syscall"

	"github.com/dogmatiq/ferrite"
	"github.com/gin-gonic/gin"
)

func createApplication(ctx context.Context, applicationBooter func()) {
	for {
		select {
		case <-ctx.Done():
			return
		default:
			applicationBooter()
		}
	}
}

func main() {
	gin.SetMode(gin.ReleaseMode)
	log.Println(utilities.Info("Validating..."))
	ferrite.Init()

	log.Println(utilities.Info("Booting..."))
	bootstrap.NewAbout()

	ctx, cancel := context.WithCancel(context.Background())

	signalChannel := make(chan os.Signal, 1)
	signal.Notify(signalChannel, os.Interrupt, syscall.SIGTERM)

	go func () {
		<-signalChannel
		log.Println(utilities.Info("Received interrupt signal, shutting down..."))
		cancel()
	}()

	go createApplication(ctx, bootstrap.NewServer)
	go createApplication(ctx, bootstrap.NewProxy)

	<-ctx.Done()
	log.Println(utilities.Info("All applications stopped. Good bye"))
}
