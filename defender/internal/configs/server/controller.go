package server

import (
	"fmt"
	"strings"

	"defly-defender/internal/controllers"
	"github.com/gin-gonic/gin"
)

type Controller struct {
	Path          Path
	Method        Method
	Authorization Authorization
	Principle     *controllers.Principle
	Decision      *controllers.Decision
}

func (c Controller) register(group *gin.RouterGroup, method, path string, handlers ...gin.HandlerFunc) {
	group.Handle(strings.ToUpper(method), fmt.Sprintf("/%s", path), handlers...)
}

func (c Controller) prefix(server *gin.Engine) *gin.RouterGroup {
	return server.Group(fmt.Sprintf("/%s", c.Path.Prefix))
}

func (c Controller) principles(group *gin.RouterGroup) {
	c.register(group, c.Method.Apply, c.Path.Principles, c.Authorization.Apply(), c.Principle.Apply)
	c.register(group, c.Method.Revoke, c.Path.Principles, c.Authorization.Revoke(), c.Principle.Revoke)
}

func (c Controller) decisions(group *gin.RouterGroup) {
	c.register(group, c.Method.Implement, c.Path.Decisions, c.Authorization.Implement(), c.Decision.Implement)
	c.register(group, c.Method.Suspend, c.Path.Decisions, c.Authorization.Suspend(), c.Decision.Suspend)
}

func (c Controller) Control(server *gin.Engine) {
	group := c.prefix(server)
	c.principles(group)
	c.decisions(group)
}
