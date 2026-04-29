package controllers

import "github.com/gin-gonic/gin"

type Policy struct{}

func (p *Policy) Apply(ctx *gin.Context) {
	ctx.JSON(501, gin.H{
		"error": "not implemented",
	})
}

func (p *Policy) Revoke(ctx *gin.Context) {
	ctx.JSON(501, gin.H{
		"error": "not implemented",
	})
}
