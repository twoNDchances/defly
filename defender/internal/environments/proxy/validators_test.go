package proxy

import "testing"

func TestValidateTrustedProxyList(t *testing.T) {
	tests := map[string]struct {
		value string
		want  bool
	}{
		"empty": {
			value: "",
			want:  false,
		},
		"blank": {
			value: "   ",
			want:  false,
		},
		"single IPv4": {
			value: "127.0.0.1",
			want:  true,
		},
		"single IPv6": {
			value: "2001:db8::1",
			want:  true,
		},
		"single CIDR": {
			value: "10.0.0.0/8",
			want:  true,
		},
		"mixed list": {
			value: "127.0.0.1, 10.0.0.0/8, 2001:db8::/32",
			want:  true,
		},
		"empty item": {
			value: "127.0.0.1,",
			want:  false,
		},
		"invalid IP": {
			value: "not-an-ip",
			want:  false,
		},
		"invalid prefix": {
			value: "10.0.0.0/33",
			want:  false,
		},
	}

	for name, tt := range tests {
		t.Run(name, func(t *testing.T) {
			if got := validateTrustedProxyList(tt.value); got != tt.want {
				t.Fatalf("validateTrustedProxyList(%q) = %t, want %t", tt.value, got, tt.want)
			}
		})
	}
}
