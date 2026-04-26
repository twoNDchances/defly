package config

import (
	"bytes"
	"fmt"
	"log"
	"net"
	"os"
	"strings"

	"defly-defender/internal/controllers"
	"defly-defender/internal/globals"
	"defly-defender/internal/utilities"

	"github.com/gin-gonic/gin"
	"go.yaml.in/yaml/v3"
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

type Security struct {
	Manager  string
	Username string
	Password string
}

func (s Security) Secure(server *gin.Engine) error {
	managerIPs := map[string]bool{}
	if ip := net.ParseIP(s.Manager); ip != nil {
		managerIPs[ip.String()] = true
	} else {
		addresses, err := net.LookupHost(s.Manager)
		if err == nil {
			for _, address := range addresses {
				managerIPs[address] = true
			}
		}
	}

	server.Use(func(ctx *gin.Context) {
		clientIP := ctx.ClientIP()
		host := ctx.Request.Host
		hostWithoutPort, _, _ := strings.Cut(host, ":")

		if clientIP != s.Manager && !managerIPs[clientIP] && !strings.EqualFold(host, s.Manager) && !strings.EqualFold(hostWithoutPort, s.Manager) {
			ctx.AbortWithStatusJSON(403, gin.H{
				"error": "manager is not allowed",
			})
			return
		}
		ctx.Next()
	})

	server.Use(gin.BasicAuthForRealm(gin.Accounts{
		s.Username: s.Password,
	}, "Defly Defender"))

	return nil
}

type Path struct {
	Prefix    string
	State     string
	Gate      string
	Policies  string
	Decisions string
}

type Method struct {
	Check     string
	Inspect   string
	Lock      string
	Unlock    string
	Apply     string
	Revoke    string
	Implement string
	Suspend   string
}

type Controller struct {
	Path     Path
	Method   Method
	State    *controllers.State
	Gate     *controllers.Gate
	Policy   *controllers.Policy
	Decision *controllers.Decision
}

func (c Controller) register(group *gin.RouterGroup, method, path string, handlers ...gin.HandlerFunc) {
	group.Handle(strings.ToUpper(method), fmt.Sprintf("/%s", path), handlers...)
}

func (c Controller) prefix(server *gin.Engine) *gin.RouterGroup {
	return server.Group(fmt.Sprintf("/%s", c.Path.Prefix))
}

func (c Controller) state(group *gin.RouterGroup) {
	c.register(group, c.Method.Check, c.Path.State, c.State.Check)
	c.register(group, c.Method.Inspect, c.Path.State, c.State.Inspect)
}

func (c Controller) gate(group *gin.RouterGroup) {
	c.register(group, c.Method.Lock, c.Path.Gate, c.Gate.Lock)
	c.register(group, c.Method.Unlock, c.Path.Gate, c.Gate.Unlock)
}

func (c Controller) policies(group *gin.RouterGroup) {
	c.register(group, c.Method.Apply, c.Path.Policies, c.Policy.Apply)
	c.register(group, c.Method.Revoke, c.Path.Policies, c.Policy.Revoke)
}

func (c Controller) decisions(group *gin.RouterGroup) {
	c.register(group, c.Method.Implement, c.Path.Decisions, c.Decision.Implement)
	c.register(group, c.Method.Suspend, c.Path.Decisions, c.Decision.Suspend)
}

func (c Controller) Control(server *gin.Engine) {
	group := c.prefix(server)
	c.state(group)
	c.gate(group)
	c.policies(group)
	c.decisions(group)
}

type Data struct {
	Policies  []globals.Policy   `yaml:"policies"`
	Decisions []globals.Decision `yaml:"decisions"`
}

type Storage struct {
	Type string
	Path string
	Data *Data
}

func (s *Storage) Load() error {
	if s.Data == nil {
		s.Data = &Data{}
	}

	if s.Type == "file" {
		file, err := utilities.CreateFileIfNotExists(s.Path)
		if err != nil {
			return err
		}
		if err := file.Close(); err != nil {
			return err
		}

		raw, err := os.ReadFile(s.Path)
		if err != nil {
			return err
		}

		if len(bytes.TrimSpace(raw)) > 0 {
			if err := yaml.Unmarshal(raw, s.Data); err != nil {
				return err
			}
		}
	}
	if s.Data.Policies == nil {
		s.Data.Policies = []globals.Policy{}
	}
	if s.Data.Decisions == nil {
		s.Data.Decisions = []globals.Decision{}
	}

	globals.Pauser.Lock()
	defer globals.Pauser.Unlock()

	globals.Policies = &s.Data.Policies
	globals.Decisions = &s.Data.Decisions

	return nil
}

func (s *Storage) persist() error {
	if s.Type != "file" {
		return nil
	}

	if s.Data == nil {
		s.Data = &Data{}
	}

	if globals.Policies != nil {
		s.Data.Policies = *globals.Policies
	}
	if globals.Decisions != nil {
		s.Data.Decisions = *globals.Decisions
	}

	raw, err := yaml.Marshal(s.Data)
	if err != nil {
		return err
	}

	return os.WriteFile(s.Path, raw, 0666)
}

type PolicyStore struct {
	Storage *Storage
}

func (p PolicyStore) Set(items []globals.Policy) error {
	globals.Pauser.Lock()
	defer globals.Pauser.Unlock()

	if globals.Policies == nil {
		policies := []globals.Policy{}
		globals.Policies = &policies
	}

	for _, item := range items {
		found := false
		for index := range *globals.Policies {
			if (*globals.Policies)[index].Id == item.Id {
				(*globals.Policies)[index] = item
				found = true
				break
			}
		}
		if !found {
			*globals.Policies = append(*globals.Policies, item)
		}
	}

	return p.Storage.persist()
}

func (p PolicyStore) Unset(ids []string) ([]string, error) {
	globals.Pauser.Lock()
	defer globals.Pauser.Unlock()

	if globals.Policies == nil {
		return []string{}, nil
	}

	idSet := map[string]bool{}
	for _, id := range ids {
		idSet[id] = true
	}

	revoked := []string{}
	policies := (*globals.Policies)[:0]
	for _, policy := range *globals.Policies {
		if idSet[policy.Id] {
			revoked = append(revoked, policy.Id)
			continue
		}
		policies = append(policies, policy)
	}

	if len(revoked) == 0 {
		return revoked, nil
	}

	*globals.Policies = policies
	return revoked, p.Storage.persist()
}

type DecisionStore struct {
	Storage *Storage
}

func (d DecisionStore) Set(items []globals.Decision) error {
	globals.Pauser.Lock()
	defer globals.Pauser.Unlock()

	if globals.Decisions == nil {
		decisions := []globals.Decision{}
		globals.Decisions = &decisions
	}

	for _, item := range items {
		found := false
		for index := range *globals.Decisions {
			if (*globals.Decisions)[index].Id == item.Id {
				(*globals.Decisions)[index] = item
				found = true
				break
			}
		}
		if !found {
			*globals.Decisions = append(*globals.Decisions, item)
		}
	}

	return d.Storage.persist()
}

func (d DecisionStore) Unset(ids []string) ([]string, error) {
	globals.Pauser.Lock()
	defer globals.Pauser.Unlock()

	if globals.Decisions == nil {
		return []string{}, nil
	}

	idSet := map[string]bool{}
	for _, id := range ids {
		idSet[id] = true
	}

	suspended := []string{}
	decisions := (*globals.Decisions)[:0]
	for _, decision := range *globals.Decisions {
		if idSet[decision.Id] {
			suspended = append(suspended, decision.Id)
			continue
		}
		decisions = append(decisions, decision)
	}

	if len(suspended) == 0 {
		return suspended, nil
	}

	*globals.Decisions = decisions
	return suspended, d.Storage.persist()
}

type Server struct {
	Address    Address
	Absorber   Absorber
	Tls        Tls
	Logger     Logger
	Security   Security
	Controller Controller
	Storage    Storage
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

	if err := s.Security.Secure(server); err != nil {
		return s.Error.LogError(err)
	}

	storage := &s.Storage
	if err := storage.Load(); err != nil {
		return s.Error.LogError(err)
	}
	s.Controller.State = &controllers.State{
		Policies:  globals.Policies,
		Decisions: globals.Decisions,
	}
	s.Controller.Gate = &controllers.Gate{}
	s.Controller.Policy = &controllers.Policy{
		Policies: globals.Policies,
		Store: PolicyStore{
			Storage: storage,
		},
	}
	s.Controller.Decision = &controllers.Decision{
		Decisions: globals.Decisions,
		Store: DecisionStore{
			Storage: storage,
		},
	}

	s.Controller.Control(server)

	scheme := "http"
	if s.Tls.Enable {
		scheme = "https"
	}

	log.Println(utilities.Infof("Defender server is running at %s://0.0.0.0:%s", scheme, s.Address.Port))
	return s.Error.LogError(s.Tls.Listen(server, fmt.Sprintf(":%s", s.Address.Port)))
}
