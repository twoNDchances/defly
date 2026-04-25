package logger

import (
	"os"
	"path/filepath"
	"testing"
)

func TestValidateLoggerFilePath(t *testing.T) {
	root := t.TempDir()
	existingFile := filepath.Join(root, "server.log")
	if err := os.WriteFile(existingFile, []byte("log"), 0o600); err != nil {
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
		"new file": {
			value: filepath.Join(root, "logs", "proxy.log"),
			want:  true,
		},
		"existing directory": {
			value: root,
			want:  false,
		},
	}

	for name, tt := range tests {
		t.Run(name, func(t *testing.T) {
			if got := validateLoggerFilePath(tt.value); got != tt.want {
				t.Fatalf("validateLoggerFilePath(%q) = %t, want %t", tt.value, got, tt.want)
			}
		})
	}
}
