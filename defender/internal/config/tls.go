package config

import (
	"crypto/rand"
	"crypto/rsa"
	"crypto/tls"
	"crypto/x509"
	"crypto/x509/pkix"
	"encoding/pem"
	"fmt"
	"math/big"
	"net"
	"os"
	"path/filepath"
	"strings"
	"time"

	"github.com/gin-gonic/gin"
)

const (
	defaultTLSDirectoryPath = "storage/tls"
	defaultTLSName          = "defender"
	defaultTLSValidDays     = 365
	defaultTLSKeySize       = 2048
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
	if err := ensureTLSCertificatePair(certPath, keyPath, t.certificateName()); err != nil {
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

func ensureTLSCertificatePair(certPath, keyPath, name string) error {
	loadErr := validateTLSCertificatePair(certPath, keyPath)
	if loadErr == nil {
		return nil
	}

	certExists, certErr := fileExists(certPath)
	if certErr != nil {
		return fmt.Errorf("failed to inspect TLS certificate: %w", certErr)
	}

	keyExists, keyErr := fileExists(keyPath)
	if keyErr != nil {
		return fmt.Errorf("failed to inspect TLS key: %w", keyErr)
	}

	if certExists && keyExists {
		return fmt.Errorf("failed to load TLS certificate pair: %w", loadErr)
	}

	if err := generateSelfSignedTLSCertificate(certPath, keyPath, name); err != nil {
		return fmt.Errorf("failed to generate TLS certificate pair: %w", err)
	}

	if err := validateTLSCertificatePair(certPath, keyPath); err != nil {
		return fmt.Errorf("generated TLS certificate pair is invalid: %w", err)
	}

	return nil
}

func validateTLSCertificatePair(certPath, keyPath string) error {
	_, err := tls.LoadX509KeyPair(certPath, keyPath)
	return err
}

func generateSelfSignedTLSCertificate(certPath, keyPath, name string) error {
	key, err := rsa.GenerateKey(rand.Reader, defaultTLSKeySize)
	if err != nil {
		return err
	}

	serialNumber, err := rand.Int(rand.Reader, new(big.Int).Lsh(big.NewInt(1), 128))
	if err != nil {
		return err
	}

	template := x509.Certificate{
		SerialNumber: serialNumber,
		Subject: pkix.Name{
			CommonName:   name,
			Organization: []string{"Defly Defender"},
		},
		NotBefore:             time.Now().Add(-1 * time.Hour),
		NotAfter:              time.Now().Add(defaultTLSValidDays * 24 * time.Hour),
		KeyUsage:              x509.KeyUsageDigitalSignature | x509.KeyUsageKeyEncipherment,
		ExtKeyUsage:           []x509.ExtKeyUsage{x509.ExtKeyUsageServerAuth},
		BasicConstraintsValid: true,
		DNSNames:              []string{"localhost", name},
		IPAddresses:           []net.IP{net.ParseIP("127.0.0.1")},
	}

	certDER, err := x509.CreateCertificate(rand.Reader, &template, &template, &key.PublicKey, key)
	if err != nil {
		return err
	}

	certPEM := pem.EncodeToMemory(&pem.Block{Type: "CERTIFICATE", Bytes: certDER})
	if certPEM == nil {
		return fmt.Errorf("failed to encode TLS certificate")
	}

	keyDER := x509.MarshalPKCS1PrivateKey(key)
	keyPEM := pem.EncodeToMemory(&pem.Block{Type: "RSA PRIVATE KEY", Bytes: keyDER})
	if keyPEM == nil {
		return fmt.Errorf("failed to encode TLS key")
	}

	if err := ensureParentDir(certPath); err != nil {
		return err
	}

	if err := ensureParentDir(keyPath); err != nil {
		return err
	}

	if err := os.WriteFile(keyPath, keyPEM, 0o600); err != nil {
		return err
	}

	return os.WriteFile(certPath, certPEM, 0o644)
}

func ensureParentDir(path string) error {
	dir := filepath.Dir(path)
	if dir == "." || dir == "" {
		return nil
	}

	return os.MkdirAll(dir, 0o755)
}

func fileExists(path string) (bool, error) {
	info, err := os.Stat(path)
	if err == nil {
		if info.IsDir() {
			return false, fmt.Errorf("%s is a directory", path)
		}

		return true, nil
	}

	if os.IsNotExist(err) {
		return false, nil
	}

	return false, err
}
