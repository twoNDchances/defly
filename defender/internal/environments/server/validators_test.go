package server

import "testing"

func TestValidateSecurityManager(t *testing.T) {
	tests := map[string]struct {
		value string
		want  bool
	}{
		"host": {
			value: "manager",
			want:  true,
		},
		"host with port": {
			value: "manager:8080",
			want:  false,
		},
		"ipv4": {
			value: "127.0.0.1",
			want:  true,
		},
		"empty": {
			value: "",
			want:  false,
		},
		"space": {
			value: "manager host",
			want:  false,
		},
		"url": {
			value: "https://manager",
			want:  false,
		},
	}

	for name, tt := range tests {
		t.Run(name, func(t *testing.T) {
			if got := validateSecurityManager(tt.value); got != tt.want {
				t.Fatalf("validateSecurityManager(%q) = %t, want %t", tt.value, got, tt.want)
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

func TestValidateServerHeaderName(t *testing.T) {
	tests := map[string]struct {
		value string
		want  bool
	}{
		"default": {
			value: "X-Executor",
			want:  true,
		},
		"underscore": {
			value: "X_Executor",
			want:  true,
		},
		"empty": {
			value: "",
			want:  false,
		},
		"space": {
			value: "X Executor",
			want:  false,
		},
		"colon": {
			value: "X-Executor:",
			want:  false,
		},
		"slash": {
			value: "X/Executor",
			want:  false,
		},
	}

	for name, tt := range tests {
		t.Run(name, func(t *testing.T) {
			if got := validateServerHeaderName(tt.value); got != tt.want {
				t.Fatalf("validateServerHeaderName(%q) = %t, want %t", tt.value, got, tt.want)
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
				"SERVER_PATH_STATE":      "state",
				"SERVER_PATH_GATE":       "gate",
				"SERVER_PATH_PRINCIPLES": "principles",
				"SERVER_PATH_DECISIONS":  "decisions",
			},
			wantErr: false,
		},
		"duplicate": {
			values: map[string]string{
				"SERVER_PATH_STATE":      "state",
				"SERVER_PATH_GATE":       "gate",
				"SERVER_PATH_PRINCIPLES": "gate",
				"SERVER_PATH_DECISIONS":  "decisions",
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
