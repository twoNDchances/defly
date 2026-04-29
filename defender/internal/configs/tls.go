package configs

import (
	"path/filepath"
	"strings"

	"defly-defender/internal/utilities"
	"github.com/gin-gonic/gin"
)

const (
	defaultTLSDirectoryPath = "storage/tls"
	defaultTLSName          = "defender"
)

type Tls struct {
	Enable bool
	Name   string
}

func (t Tls) Listen(application *gin.Engine, address string) error {
	if !t.Enable {
		return application.Run(address)
	}

	certPath, keyPath := t.certificatePairPaths()
	if err := utilities.EnsureTLSCertificatePair(certPath, keyPath, t.certificateName()); err != nil {
		return err
	}

	return application.RunTLS(address, certPath, keyPath)
}

func (t Tls) certificateName() string {
	name := strings.TrimSpace(t.Name)
	if name == "" {
		return defaultTLSName
	}
	return name
}

func (t Tls) certificatePairPaths() (string, string) {
	name := t.certificateName()
	return filepath.Join(defaultTLSDirectoryPath, name+".crt"), filepath.Join(defaultTLSDirectoryPath, name+".key")
}
