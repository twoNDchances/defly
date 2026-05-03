package rules

import (
	"encoding/json"
	"fmt"
	"math"
	"regexp"
	"slices"
	"strconv"
	"strings"

	"defly-defender/ent"
)

type TargetExtractor interface {
	Extract(target *ent.Target, phase int) any
}

type TargetTransformer interface {
	Transform(value any, target *ent.Target) any
}

type WordlistLoader interface {
	Words(wordlist *ent.Wordlist) []string
}

type Matcher struct {
	Targets  TargetExtractor
	Engines  TargetTransformer
	Wordlist WordlistLoader
}

func (m Matcher) Match(rule *ent.Rule, phase int) bool {
	target := rule.Edges.Target
	value := any(nil)
	if target != nil {
		value = m.Targets.Extract(target, phase)
		value = m.Engines.Transform(value, target)
	}
	return compare(rule.Comparator, value, m.expectedValues(rule))
}

func (m Matcher) expectedValues(rule *ent.Rule) []any {
	expected := make([]any, 0)
	for _, key := range []string{"number", "number_from", "number_to", "string", "value", "expected", "needle", "pattern", "min", "max"} {
		if value, ok := rule.Configurations[key]; ok {
			expected = append(expected, value)
		}
	}
	if wordlist := rule.Edges.Wordlist; wordlist != nil && m.Wordlist != nil {
		for _, word := range m.Wordlist.Words(wordlist) {
			expected = append(expected, word)
		}
	}
	return expected
}

func compare(comparator string, value any, expected []any) bool {
	switch comparator {
	case "@check":
		return value != nil && stringify(value) != ""
	case "@equal":
		return slices.ContainsFunc(expected, func(item any) bool { return stringify(value) == stringify(item) })
	case "@contains", "@search":
		text := stringify(value)
		return slices.ContainsFunc(expected, func(item any) bool { return strings.Contains(text, stringify(item)) })
	case "@startsWith":
		text := stringify(value)
		return slices.ContainsFunc(expected, func(item any) bool { return strings.HasPrefix(text, stringify(item)) })
	case "@endsWith":
		text := stringify(value)
		return slices.ContainsFunc(expected, func(item any) bool { return strings.HasSuffix(text, stringify(item)) })
	case "@greaterThan":
		return toFloat(value) > firstFloat(expected)
	case "@lessThan":
		return toFloat(value) < firstFloat(expected)
	case "@greaterThanOrEqual":
		return toFloat(value) >= firstFloat(expected)
	case "@lessThanOrEqual":
		return toFloat(value) <= firstFloat(expected)
	case "@inRange":
		if len(expected) < 2 {
			return false
		}
		number := toFloat(value)
		return number >= toFloat(expected[0]) && number <= toFloat(expected[1])
	case "@match", "@regExp", "@checkRegExp":
		text := stringify(value)
		return slices.ContainsFunc(expected, func(item any) bool {
			matched, _ := regexp.MatchString(stringify(item), text)
			return matched
		})
	case "@mirror":
		items := toStrings(value)
		return len(items) > 0 && slices.ContainsFunc(items, func(item string) bool { return item == stringify(expectedValue(expected)) })
	case "@similar":
		return similarity(stringify(value), stringify(expectedValue(expected))) >= 0.8
	default:
		return false
	}
}

func stringify(value any) string {
	switch typed := value.(type) {
	case nil:
		return ""
	case string:
		return typed
	case []byte:
		return string(typed)
	case fmt.Stringer:
		return typed.String()
	default:
		return fmt.Sprint(typed)
	}
}

func toFloat(value any) float64 {
	switch typed := value.(type) {
	case int:
		return float64(typed)
	case int64:
		return float64(typed)
	case uint64:
		return float64(typed)
	case float32:
		return float64(typed)
	case float64:
		return typed
	case json.Number:
		number, _ := typed.Float64()
		return number
	default:
		number, _ := strconv.ParseFloat(strings.TrimSpace(stringify(value)), 64)
		return number
	}
}

func firstFloat(values []any) float64 {
	return toFloat(expectedValue(values))
}

func expectedValue(values []any) any {
	if len(values) == 0 {
		return nil
	}
	return values[0]
}

func toStrings(value any) []string {
	items := toAnySlice(value)
	result := make([]string, 0, len(items))
	for _, item := range items {
		result = append(result, stringify(item))
	}
	return result
}

func toAnySlice(value any) []any {
	switch typed := value.(type) {
	case nil:
		return []any{nil}
	case []any:
		return typed
	case []string:
		items := make([]any, 0, len(typed))
		for _, item := range typed {
			items = append(items, item)
		}
		return items
	default:
		return []any{typed}
	}
}

func similarity(a string, b string) float64 {
	if a == b {
		return 1
	}
	if a == "" || b == "" {
		return 0
	}
	distance := levenshtein(a, b)
	maxLen := math.Max(float64(len(a)), float64(len(b)))
	return 1 - float64(distance)/maxLen
}

func levenshtein(a string, b string) int {
	previous := make([]int, len(b)+1)
	for j := range previous {
		previous[j] = j
	}
	for i := 1; i <= len(a); i++ {
		current := make([]int, len(b)+1)
		current[0] = i
		for j := 1; j <= len(b); j++ {
			cost := 0
			if a[i-1] != b[j-1] {
				cost = 1
			}
			current[j] = min(previous[j]+1, current[j-1]+1, previous[j-1]+cost)
		}
		previous = current
	}
	return previous[len(b)]
}
