package controllers

import (
	"strings"

	"defly-defender/internal/globals"

	"github.com/gin-gonic/gin"
)

type Decision struct {
	Decisions *[]globals.Decision
	Store     Store[globals.Decision]
}

func (d *Decision) Implement(ctx *gin.Context) {
	var decisions []globals.Decision
	if err := ctx.ShouldBind(&decisions); err != nil {
		ctx.JSON(400, gin.H{
			"error": err.Error(),
		})
		return
	}

	if len(decisions) == 0 {
		ctx.JSON(400, gin.H{
			"error": "decisions are required",
		})
		return
	}

	for index := range decisions {
		decisions[index].Id = strings.TrimSpace(decisions[index].Id)
		if decisions[index].Id == "" {
			ctx.JSON(400, gin.H{
				"error": "decision id is required",
			})
			return
		}
	}

	if d.Store == nil {
		ctx.JSON(500, gin.H{
			"error": "decision storage is not configured",
		})
		return
	}

	if err := d.Store.Set(decisions); err != nil {
		ctx.JSON(500, gin.H{
			"error": err.Error(),
		})
		return
	}

	ctx.JSON(200, gin.H{
		"decisions": decisions,
	})
}

func (d *Decision) Suspend(ctx *gin.Context) {
	var ids []string
	if err := ctx.ShouldBind(&ids); err != nil {
		ctx.JSON(400, gin.H{
			"error": err.Error(),
		})
		return
	}

	if len(ids) == 0 {
		ctx.JSON(400, gin.H{
			"error": "decision ids are required",
		})
		return
	}

	for index := range ids {
		ids[index] = strings.TrimSpace(ids[index])
		if ids[index] == "" {
			ctx.JSON(400, gin.H{
				"error": "decision id is required",
			})
			return
		}
	}

	if d.Store == nil {
		ctx.JSON(500, gin.H{
			"error": "decision storage is not configured",
		})
		return
	}

	suspended, err := d.Store.Unset(ids)
	if err != nil {
		ctx.JSON(500, gin.H{
			"error": err.Error(),
		})
		return
	}
	if len(suspended) == 0 {
		ctx.JSON(404, gin.H{
			"error": "decision not found",
		})
		return
	}

	ctx.JSON(200, gin.H{
		"ids":     suspended,
		"missing": missingIds(ids, suspended),
	})
}
