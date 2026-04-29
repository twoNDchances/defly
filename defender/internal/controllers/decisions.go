package controllers

import "github.com/gin-gonic/gin"

type Decision struct{}

func (d *Decision) Implement(ctx *gin.Context) {
	ctx.JSON(501, gin.H{
		"error": "not implemented",
	})
}

func (d *Decision) Suspend(ctx *gin.Context) {
	ctx.JSON(501, gin.H{
		"error": "not implemented",
	})
}
