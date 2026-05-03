package engines

import (
	"crypto/md5"
	"crypto/sha1"
	"crypto/sha256"
	"crypto/sha512"
	"encoding/hex"
	"strings"
)

type Hash struct {
	Method string
}

func (h Hash) Transform(value any) any {
	text := stringify(value)
	switch strings.ToLower(h.Method) {
	case "md5":
		sum := md5.Sum([]byte(text))
		return hex.EncodeToString(sum[:])
	case "sha1":
		sum := sha1.Sum([]byte(text))
		return hex.EncodeToString(sum[:])
	case "sha224":
		sum := sha256.Sum224([]byte(text))
		return hex.EncodeToString(sum[:])
	case "sha512":
		sum := sha512.Sum512([]byte(text))
		return hex.EncodeToString(sum[:])
	default:
		sum := sha256.Sum256([]byte(text))
		return hex.EncodeToString(sum[:])
	}
}
