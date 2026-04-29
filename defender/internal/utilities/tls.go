package utilities

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
	"time"
)

const (
	DefaultTLSValidDays = 365
	DefaultTLSKeySize   = 2048
)

func EnsureTLSCertificatePair(certPath, keyPath, name string) error {
	loadErr := ValidateTLSCertificatePair(certPath, keyPath)
	if loadErr == nil {
		return nil
	}

	certExists, certErr := FileExists(certPath)
	if certErr != nil {
		return fmt.Errorf("failed to inspect TLS certificate: %w", certErr)
	}

	keyExists, keyErr := FileExists(keyPath)
	if keyErr != nil {
		return fmt.Errorf("failed to inspect TLS key: %w", keyErr)
	}

	if certExists && keyExists {
		return fmt.Errorf("failed to load TLS certificate pair: %w", loadErr)
	}

	if err := GenerateSelfSignedTLSCertificate(certPath, keyPath, name); err != nil {
		return fmt.Errorf("failed to generate TLS certificate pair: %w", err)
	}

	if err := ValidateTLSCertificatePair(certPath, keyPath); err != nil {
		return fmt.Errorf("generated TLS certificate pair is invalid: %w", err)
	}

	return nil
}

func ValidateTLSCertificatePair(certPath, keyPath string) error {
	_, err := tls.LoadX509KeyPair(certPath, keyPath)
	return err
}

func GenerateSelfSignedTLSCertificate(certPath, keyPath, name string) error {
	key, err := rsa.GenerateKey(rand.Reader, DefaultTLSKeySize)
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
		NotAfter:              time.Now().Add(DefaultTLSValidDays * 24 * time.Hour),
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

	if err := EnsureParentDir(certPath); err != nil {
		return err
	}

	if err := EnsureParentDir(keyPath); err != nil {
		return err
	}

	if err := os.WriteFile(keyPath, keyPEM, 0o600); err != nil {
		return err
	}

	return os.WriteFile(certPath, certPEM, 0o644)
}
