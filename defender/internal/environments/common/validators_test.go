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

func TestValidateWordlistRoot(t *testing.T) {
	root := t.TempDir()
	filePath := filepath.Join(root, "wordlist.txt")
	if err := os.WriteFile(filePath, []byte("word"), 0o600); err != nil {
		t.Fatal(err)
	}

	tests := map[string]struct {
		value string
		want  bool
	}{
		"existing directory": {
			value: root,
			want:  true,
		},
		"new nested directory": {
			value: filepath.Join(root, "wordlists", "private"),
			want:  true,
		},
		"existing file": {
			value: filePath,
			want:  false,
		},
	}

	for name, tt := range tests {
		t.Run(name, func(t *testing.T) {
			if got := validateWordlistRoot(tt.value); got != tt.want {
				t.Fatalf("validateWordlistRoot(%q) = %t, want %t", tt.value, got, tt.want)
			}
		})
	}
}

func TestValidateDefenderName(t *testing.T) {
	tests := map[string]struct {
		value string
		want  bool
	}{
		"default": {
			value: "defender",
			want:  true,
		},
		"kebab": {
			value: "defender-1",
			want:  true,
		},
		"underscore and dot": {
			value: "defender_1.local",
			want:  true,
		},
		"empty": {
			value: "",
			want:  false,
		},
		"path traversal": {
			value: "..",
			want:  false,
		},
		"path separator": {
			value: "team/defender",
			want:  false,
		},
		"space": {
			value: "team defender",
			want:  false,
		},
	}

	for name, tt := range tests {
		t.Run(name, func(t *testing.T) {
			if got := validateDefenderName(tt.value); got != tt.want {
				t.Fatalf("validateDefenderName(%q) = %t, want %t", tt.value, got, tt.want)
			}
		})
	}
}
