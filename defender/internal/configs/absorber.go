package configs

import "github.com/gin-gonic/gin"

type Absorber struct{}

func (a Absorber) Recover(application *gin.Engine) {
	application.Use(gin.Recovery())
}
