package server

import (
	"os"
	"path/filepath"
	"testing"
)

func TestValidateTLSFilePath(t *testing.T) {
	root := t.TempDir()
	existingFile := filepath.Join(root, "tls.crt")
	if err := os.WriteFile(existingFile, []byte("certificate"), 0o600); err != nil {
		t.Fatal(err)
	}

	tests := map[string]struct {
		value string
		want  bool
	}{
		"empty": {
			value: "",
			want:  false,
		},
		"existing file": {
			value: existingFile,
			want:  true,
		},
		"missing file": {
			value: filepath.Join(root, "missing.crt"),
			want:  true,
		},
		"existing directory": {
			value: root,
			want:  false,
		},
	}

	for name, tt := range tests {
		t.Run(name, func(t *testing.T) {
			if got := validateTLSFilePath(tt.value); got != tt.want {
				t.Fatalf("validateTLSFilePath(%q) = %t, want %t", tt.value, got, tt.want)
			}
		})
	}
}
