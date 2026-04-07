package main

import (
	"crypto/rand"
	"crypto/rsa"
	"crypto/x509"
	"crypto/x509/pkix"
	"encoding/pem"
	"flag"
	"fmt"
	"math/big"
	"net"
	"os"
	"path/filepath"
	"strings"
	"time"
)

const (
	defaultCertOutput = "tls.crt"
	defaultKeyOutput  = "tls.key"
	defaultHosts      = "localhost,127.0.0.1"
	defaultValidDays  = 365
	defaultKeySize    = 2048
	defaultOrg        = "Defly Defender"
)

func main() {
	certPath := flag.String("cert-out", defaultCertOutput, "Path to output certificate file")
	keyPath := flag.String("key-out", defaultKeyOutput, "Path to output private key file")
	hostsRaw := flag.String("hosts", defaultHosts, "Comma-separated SAN hosts (DNS or IP)")
	commonName := flag.String("cn", "localhost", "Certificate subject common name")
	org := flag.String("org", defaultOrg, "Certificate organization")
	validDays := flag.Int("valid-days", defaultValidDays, "Certificate validity in days")
	keySize := flag.Int("key-size", defaultKeySize, "RSA key size")
	overwrite := flag.Bool("overwrite", false, "Overwrite output files if they already exist")
	flag.Parse()

	if *validDays <= 0 {
		failf("valid-days must be greater than 0")
	}

	if *keySize < 2048 {
		failf("key-size must be at least 2048")
	}

	hosts := parseHosts(*hostsRaw)
	if len(hosts.dnsNames) == 0 && len(hosts.ipAddresses) == 0 {
		failf("hosts must include at least one valid DNS name or IP address")
	}

	if err := ensureParentDir(*certPath); err != nil {
		failf("failed to prepare cert output path: %v", err)
	}

	if err := ensureParentDir(*keyPath); err != nil {
		failf("failed to prepare key output path: %v", err)
	}

	key, err := rsa.GenerateKey(rand.Reader, *keySize)
	if err != nil {
		failf("failed to generate private key: %v", err)
	}

	serialNumber, err := rand.Int(rand.Reader, new(big.Int).Lsh(big.NewInt(1), 128))
	if err != nil {
		failf("failed to generate serial number: %v", err)
	}

	template := x509.Certificate{
		SerialNumber: serialNumber,
		Subject: pkix.Name{
			CommonName:   *commonName,
			Organization: []string{*org},
		},
		NotBefore:             time.Now().Add(-1 * time.Hour),
		NotAfter:              time.Now().Add(time.Duration(*validDays) * 24 * time.Hour),
		KeyUsage:              x509.KeyUsageDigitalSignature | x509.KeyUsageKeyEncipherment,
		ExtKeyUsage:           []x509.ExtKeyUsage{x509.ExtKeyUsageServerAuth},
		BasicConstraintsValid: true,
		DNSNames:              hosts.dnsNames,
		IPAddresses:           hosts.ipAddresses,
	}

	certDER, err := x509.CreateCertificate(rand.Reader, &template, &template, &key.PublicKey, key)
	if err != nil {
		failf("failed to create certificate: %v", err)
	}

	if err := writePEMFile(*certPath, "CERTIFICATE", certDER, 0o644, *overwrite); err != nil {
		failf("failed to write certificate: %v", err)
	}

	keyDER := x509.MarshalPKCS1PrivateKey(key)
	if err := writePEMFile(*keyPath, "RSA PRIVATE KEY", keyDER, 0o600, *overwrite); err != nil {
		failf("failed to write private key: %v", err)
	}

	fmt.Printf("TLS certificate generated: %s\n", *certPath)
	fmt.Printf("TLS private key generated: %s\n", *keyPath)
}

type sanHosts struct {
	dnsNames    []string
	ipAddresses []net.IP
}

func parseHosts(raw string) sanHosts {
	parts := strings.Split(raw, ",")
	result := sanHosts{
		dnsNames:    make([]string, 0, len(parts)),
		ipAddresses: make([]net.IP, 0, len(parts)),
	}

	for _, part := range parts {
		host := strings.TrimSpace(part)
		if host == "" {
			continue
		}

		if ip := net.ParseIP(host); ip != nil {
			result.ipAddresses = append(result.ipAddresses, ip)
			continue
		}

		result.dnsNames = append(result.dnsNames, host)
	}

	return result
}

func ensureParentDir(path string) error {
	dir := filepath.Dir(path)
	if dir == "." || dir == "" {
		return nil
	}
	return os.MkdirAll(dir, 0o755)
}

func writePEMFile(path, pemType string, data []byte, perm os.FileMode, overwrite bool) error {
	flags := os.O_WRONLY | os.O_CREATE
	if overwrite {
		flags |= os.O_TRUNC
	} else {
		flags |= os.O_EXCL
	}

	file, err := os.OpenFile(path, flags, perm)
	if err != nil {
		if os.IsExist(err) {
			return fmt.Errorf("%s already exists (use -overwrite to replace)", path)
		}
		return err
	}
	defer file.Close()

	if err := pem.Encode(file, &pem.Block{Type: pemType, Bytes: data}); err != nil {
		return err
	}

	return nil
}

func failf(format string, args ...any) {
	fmt.Fprintf(os.Stderr, format+"\n", args...)
	os.Exit(1)
}
