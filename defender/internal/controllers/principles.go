package controllers

import "github.com/gin-gonic/gin"

type Principle struct{}

func (p *Principle) Apply(ctx *gin.Context) {
	ctx.JSON(501, gin.H{
		"error": "not implemented",
	})
}

func (p *Principle) Revoke(ctx *gin.Context) {
	ctx.JSON(501, gin.H{
		"error": "not implemented",
	})
}
