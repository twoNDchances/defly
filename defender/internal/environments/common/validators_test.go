package common

import (
	"os"
	"path/filepath"
	"testing"
)

func TestValidateErrorFilePath(t *testing.T) {
	root := t.TempDir()
	filePath := filepath.Join(root, "error.log")
	if err := os.WriteFile(filePath, []byte("error"), 0o600); err != nil {
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
		"existing directory": {
			value: root,
			want:  true,
		},
		"new nested directory": {
			value: filepath.Join(root, "errors", "runtime"),
			want:  true,
		},
		"existing file": {
			value: filePath,
			want:  false,
		},
	}

	for name, tt := range tests {
		t.Run(name, func(t *testing.T) {
			if got := validateErrorDirectoryPath(tt.value); got != tt.want {
				t.Fatalf("validateErrorFilePath(%q) = %t, want %t", tt.value, got, tt.want)
			}
		})
	}
}
