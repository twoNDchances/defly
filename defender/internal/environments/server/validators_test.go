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

func TestValidateServerPath(t *testing.T) {
	tests := map[string]struct {
		value string
		want  bool
	}{
		"simple": {
			value: "path-prefix-v1",
			want:  true,
		},
		"nested": {
			value: "api/v1",
			want:  true,
		},
		"empty": {
			value: "",
			want:  false,
		},
		"leading slash": {
			value: "/api/v1",
			want:  false,
		},
		"trailing slash": {
			value: "api/v1/",
			want:  false,
		},
		"space": {
			value: "api v1",
			want:  false,
		},
		"empty segment": {
			value: "api//v1",
			want:  false,
		},
		"query string": {
			value: "api/v1?x=1",
			want:  false,
		},
		"current directory segment": {
			value: "api/./v1",
			want:  false,
		},
		"parent directory segment": {
			value: "api/../v1",
			want:  false,
		},
	}

	for name, tt := range tests {
		t.Run(name, func(t *testing.T) {
			if got := validateServerPath(tt.value); got != tt.want {
				t.Fatalf("validateServerPath(%q) = %t, want %t", tt.value, got, tt.want)
			}
		})
	}
}

func TestValidateDistinctValues(t *testing.T) {
	tests := map[string]struct {
		values  map[string]string
		wantErr bool
	}{
		"unique": {
			values: map[string]string{
				"SERVER_PATH_STATE":     "state",
				"SERVER_PATH_POLICIES":  "policies",
				"SERVER_PATH_DECISIONS": "decisions",
			},
			wantErr: false,
		},
		"duplicate": {
			values: map[string]string{
				"SERVER_PATH_STATE":     "state",
				"SERVER_PATH_POLICIES":  "state",
				"SERVER_PATH_DECISIONS": "decisions",
			},
			wantErr: true,
		},
		"case insensitive duplicate": {
			values: map[string]string{
				"SERVER_METHOD_CHECK":   "get",
				"SERVER_METHOD_INSPECT": "GET",
			},
			wantErr: true,
		},
	}

	for name, tt := range tests {
		t.Run(name, func(t *testing.T) {
			err := validateDistinctValues("test", tt.values)
			if (err != nil) != tt.wantErr {
				t.Fatalf("validateDistinctValues() error = %v, wantErr %t", err, tt.wantErr)
			}
		})
	}
}
