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

func createApplication(ctx context.Context, cancel context.CancelFunc, applicationBooter func() error) {
	for {
		select {
		case <-ctx.Done():
			return
		default:
			if err := applicationBooter(); err != nil {
				log.Println(err)
				cancel()
			}
		}
	}
}

func main() {
	gin.SetMode(gin.ReleaseMode)
	log.Println(utilities.Info("Validating..."))
	ferrite.Init()

	log.Println(utilities.Info("Booting..."))
	if err := bootstrap.NewAbout(); err != nil {
		log.Println(utilities.Dangerf("[compile] %v", err))
		os.Exit(1)
	}

	ctx, cancel := context.WithCancel(context.Background())

	signalChannel := make(chan os.Signal, 1)
	signal.Notify(signalChannel, os.Interrupt, syscall.SIGTERM)

	go func() {
		<-signalChannel
		log.Println(utilities.Info("Received interrupt signal, shutting down..."))
		cancel()
	}()

	go createApplication(ctx, cancel, bootstrap.NewServer)
	go createApplication(ctx, cancel, bootstrap.NewProxy)

	<-ctx.Done()
	log.Println(utilities.Info("All applications stopped. Good bye"))
}
