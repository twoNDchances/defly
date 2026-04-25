package proxy

import (
	"net/netip"
	"strings"
)

func validateTrustedProxyList(value string) bool {
	value = strings.TrimSpace(value)
	if value == "" {
		return false
	}

	for part := range strings.SplitSeq(value, ",") {
		part = strings.TrimSpace(part)
		if part == "" {
			return false
		}
		if _, err := netip.ParseAddr(part); err == nil {
			continue
		}
		if _, err := netip.ParsePrefix(part); err == nil {
			continue
		}
		return false
	}

	return true
}
