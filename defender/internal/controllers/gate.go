package controllers

import (
	"sync"

	"defly-defender/internal/globals"

	"github.com/gin-gonic/gin"
)

var (
	gatekeeper sync.Mutex
	gateLocked bool
)

type Gate struct{}

func (g *Gate) Lock(ctx *gin.Context) {
	gatekeeper.Lock()
	defer gatekeeper.Unlock()

	if gateLocked {
		ctx.JSON(423, gin.H{
			"error": "gate is already locked",
		})
		return
	}

	globals.Gate.Lock()
	gateLocked = true

	ctx.JSON(200, gin.H{
		"locked": true,
	})
}

func (g *Gate) Unlock(ctx *gin.Context) {
	gatekeeper.Lock()
	defer gatekeeper.Unlock()

	if !gateLocked {
		ctx.JSON(409, gin.H{
			"error": "gate is not locked",
		})
		return
	}

	gateLocked = false
	globals.Gate.Unlock()

	ctx.JSON(200, gin.H{
		"locked": false,
	})
}
