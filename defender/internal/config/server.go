package config

import (
	"defly-defender/internal/utilities"
	"fmt"
	"log"
	"strings"

	"github.com/gin-gonic/gin"
)

type Tls struct {
	Enable      bool
	Certificate string
	Key         string
}

func (t Tls) Listen(application *gin.Engine, address string) error {
	if t.Enable {
		return application.RunTLS(address, t.Certificate, t.Key)
	}
	return application.Run(address)
}

type Path struct {
	Prefix    string
	State     string
	Policies  string
	Decisions string
}

type Method struct {
	Check     string
	Inspect   string
	Apply     string
	Revoke    string
	Implement string
	Suspend   string
}

type Controller struct {
	Path   Path
	Method Method
}

func (c Controller) register(group *gin.RouterGroup, method, path string, handlers ...gin.HandlerFunc) {
	group.Handle(strings.ToUpper(method), fmt.Sprintf("/%s", path), handlers...)
}

func (c Controller) state(group *gin.RouterGroup) {
	c.register(group, c.Method.Check, c.Path.State)
	c.register(group, c.Method.Inspect, c.Path.State)
}

func (c Controller) policies(group *gin.RouterGroup) {
	c.register(group, c.Method.Apply, c.Path.Policies)
	c.register(group, c.Method.Revoke, c.Path.Policies)
}

func (c Controller) decisions(group *gin.RouterGroup) {
	c.register(group, c.Method.Implement, c.Path.Decisions)
	c.register(group, c.Method.Suspend, c.Path.Decisions)
}

func (c Controller) prefix(server *gin.Engine) *gin.RouterGroup {
	return server.Group(fmt.Sprintf("/%s", c.Path.Prefix))
}

func (c Controller) Control(server *gin.Engine) {
	group := c.prefix(server)
	c.state(group)
	c.policies(group)
	c.decisions(group)
}

type Server struct {
	Address    Address
	Absorber   Absorber
	Tls        Tls
	Logger     Logger
	Locker     Locker
	Controller Controller
	Error      Error
}

func (s Server) Boot() error {
	server := gin.New()

	s.Absorber.Recover(server)

	errorFile, err := s.Error.Boot()
	if err != nil {
		return fmt.Errorf("%s", s.Error.Format(err.Error()))
	}
	if errorFile != nil {
		defer errorFile.Close()
	}

	file, err := s.Logger.Boot(server)
	if err != nil {
		return s.Error.LogError(err)
	}
	if file != nil {
		defer file.Close()
	}

	s.Locker.Lock(server)
	s.Controller.Control(server)

	scheme := "http"
	if s.Tls.Enable {
		scheme = "https"
	}

	log.Println(utilities.Infof("Defender server is running at %s://0.0.0.0:%s", scheme, s.Address.Port))
	return s.Error.LogError(s.Tls.Listen(server, fmt.Sprintf(":%s", s.Address.Port)))
}
