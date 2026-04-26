package controllers

import (
	"defly-defender/internal/globals"

	"github.com/gin-gonic/gin"
)

type State struct {
	Policies *[]globals.Policy
	Decisions *[]globals.Decision
}

func (s *State) Check(ctx *gin.Context) {
	ctx.JSON(200, gin.H{
		"message": "ok",
	})
}

func (s *State) Inspect(ctx *gin.Context) {
	globals.Pauser.RLock()
	defer globals.Pauser.RUnlock()
	ctx.JSON(200, gin.H{
		"policies": s.Policies,
		"decisions": s.Decisions,
	})
}
