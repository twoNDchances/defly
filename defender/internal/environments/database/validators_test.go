package database

import "testing"

func TestValidateDatabaseHost(t *testing.T) {
	tests := map[string]struct {
		value string
		want  bool
	}{
		"hostname": {
			value: "mysql",
			want:  true,
		},
		"ipv4": {
			value: "127.0.0.1",
			want:  true,
		},
		"ipv6": {
			value: "2001:db8::1",
			want:  true,
		},
		"postgres socket path": {
			value: "/var/run/postgresql",
			want:  true,
		},
		"empty": {
			value: "",
			want:  false,
		},
		"blank": {
			value: "   ",
			want:  false,
		},
		"space": {
			value: "db host",
			want:  false,
		},
	}

	for name, tt := range tests {
		t.Run(name, func(t *testing.T) {
			if got := validateDatabaseHost(tt.value); got != tt.want {
				t.Fatalf("validateDatabaseHost(%q) = %t, want %t", tt.value, got, tt.want)
			}
		})
	}
}

func TestValidateDatabaseName(t *testing.T) {
	tests := map[string]struct {
		value string
		want  bool
	}{
		"default": {
			value: "defly_manager",
			want:  true,
		},
		"sqlite dsn": {
			value: "file:storage/defly.db?mode=rw",
			want:  true,
		},
		"empty": {
			value: "",
			want:  false,
		},
		"blank": {
			value: "   ",
			want:  false,
		},
		"space": {
			value: "defly manager",
			want:  false,
		},
	}

	for name, tt := range tests {
		t.Run(name, func(t *testing.T) {
			if got := validateDatabaseName(tt.value); got != tt.want {
				t.Fatalf("validateDatabaseName(%q) = %t, want %t", tt.value, got, tt.want)
			}
		})
	}
}

func TestValidateDatabaseUser(t *testing.T) {
	tests := map[string]struct {
		value string
		want  bool
	}{
		"root": {
			value: "root",
			want:  true,
		},
		"service user": {
			value: "defly-service",
			want:  true,
		},
		"empty": {
			value: "",
			want:  false,
		},
		"blank": {
			value: "   ",
			want:  false,
		},
		"space": {
			value: "defly service",
			want:  false,
		},
	}

	for name, tt := range tests {
		t.Run(name, func(t *testing.T) {
			if got := validateDatabaseUser(tt.value); got != tt.want {
				t.Fatalf("validateDatabaseUser(%q) = %t, want %t", tt.value, got, tt.want)
			}
		})
	}
}
