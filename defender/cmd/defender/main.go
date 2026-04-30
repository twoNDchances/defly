package main

import (
	"context"
	"defly-defender/internal/bootstrap"
	"defly-defender/internal/utilities"
	"log"
	"os"
	"os/signal"
	"syscall"
	"time"

	"github.com/dogmatiq/ferrite"
	"github.com/gin-gonic/gin"
)

func createApplication(ctx context.Context, cancel context.CancelFunc, applicationBooter func() error, messageBooters ...string) {
	if len(messageBooters) > 0 {
		for _, messageBooter := range messageBooters {
			log.Println(utilities.Info(messageBooter))
		}
	}
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
	defer cancel()

	database := bootstrap.NewDatabase()
	loadCtx, loadCancel := context.WithTimeout(ctx, 10*time.Second)
	defer loadCancel()

	if err := database.LoadGlobals(loadCtx); err != nil {
		log.Println(utilities.Dangerf("[prepare] %v", err))
		os.Exit(1)
	}
	log.Println(utilities.Info("Database loaded. Starting applications..."))

	signalChannel := make(chan os.Signal, 1)
	signal.Notify(signalChannel, os.Interrupt, syscall.SIGTERM)
	defer signal.Stop(signalChannel)

	go func() {
		<-signalChannel
		log.Println(utilities.Info("Received interrupt signal, shutting down..."))
		cancel()
	}()

	go createApplication(ctx, cancel, bootstrap.NewServer)
	go createApplication(ctx, cancel, bootstrap.NewProxy)
	go createApplication(ctx, cancel, bootstrap.NewDoctor, "Defender doctor is running")

	<-ctx.Done()
	log.Println(utilities.Info("All applications stopped. Good bye"))
}
