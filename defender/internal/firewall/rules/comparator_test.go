package rules

import "testing"

func TestCompareStringComparators(t *testing.T) {
	tests := []struct {
		name       string
		comparator string
		value      any
		expected   []any
		want       bool
	}{
		{"contains array item", "@contains", []string{"alpha", "beta"}, []any{"beta"}, true},
		{"contains misses", "@contains", []string{"alpha"}, []any{"beta"}, false},
		{"regexp match", "@match", "abc123", []any{`[a-z]+\d+`}, true},
		{"starts with", "@startsWith", "Bearer token", []any{"Bearer"}, true},
		{"ends with", "@endsWith", "index.php", []any{".php"}, true},
	}

	for _, tt := range tests {
		t.Run(tt.name, func(t *testing.T) {
			if got := Compare(tt.comparator, tt.value, tt.expected); got != tt.want {
				t.Fatalf("Compare() = %v, want %v", got, tt.want)
			}
		})
	}
}

func TestCompareNumberComparators(t *testing.T) {
	tests := []struct {
		name       string
		comparator string
		value      any
		expected   []any
		want       bool
	}{
		{"equal", "@equal", "10", []any{10}, true},
		{"greater than", "@greaterThan", 11, []any{10}, true},
		{"less than or equal", "@lessThanOrEqual", 10, []any{10}, true},
		{"in range", "@inRange", 5, []any{3, 7}, true},
		{"out of range", "@inRange", 9, []any{3, 7}, false},
	}

	for _, tt := range tests {
		t.Run(tt.name, func(t *testing.T) {
			if got := Compare(tt.comparator, tt.value, tt.expected); got != tt.want {
				t.Fatalf("Compare() = %v, want %v", got, tt.want)
			}
		})
	}
}
