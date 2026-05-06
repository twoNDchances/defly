package rules

import (
	"encoding/json"
	"fmt"
	"regexp"
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
	return Compare(rule.Comparator, value, m.expectedValues(rule))
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

func Compare(comparator string, value any, expected []any) bool {
	switch comparator {
	case "@similar", "@contains":
		return anyArrayItemMatches(value, expected, stringsEqual)
	case "@match", "@search":
		return anyArrayItemMatches(value, expected, regexpMatches)
	case "@check", "@mirror":
		return anyExpectedMatches(value, expected, stringsEqual)
	case "@regExp", "@checkRegExp":
		return anyExpectedMatches(value, expected, regexpMatches)
	case "@startsWith":
		return anyExpectedMatches(value, expected, strings.HasPrefix)
	case "@endsWith":
		return anyExpectedMatches(value, expected, strings.HasSuffix)
	case "@equal":
		return anyExpectedMatches(value, expected, numbersEqual)
	case "@greaterThan":
		return compareFirstNumber(value, expected, func(number, limit float64) bool { return number > limit })
	case "@lessThan":
		return compareFirstNumber(value, expected, func(number, limit float64) bool { return number < limit })
	case "@greaterThanOrEqual":
		return compareFirstNumber(value, expected, func(number, limit float64) bool { return number >= limit })
	case "@lessThanOrEqual":
		return compareFirstNumber(value, expected, func(number, limit float64) bool { return number <= limit })
	case "@inRange":
		return compareRangeNumber(value, expected)
	default:
		return false
	}
}

func MatchedExpectedValues(comparator string, value any, expected []any) []any {
	matches := make([]any, 0)
	for _, item := range expected {
		if expectedMatches(comparator, value, item, expected) {
			matches = append(matches, item)
		}
	}
	return matches
}

func MatchedTargetValues(comparator string, value any, expected []any) []any {
	matches := make([]any, 0)
	for _, item := range toAnySlice(value) {
		if Compare(comparator, item, expected) {
			matches = append(matches, item)
		}
	}
	return matches
}

func expectedMatches(comparator string, value any, expected any, allExpected []any) bool {
	switch comparator {
	case "@similar", "@contains":
		return anyArrayItemMatches(value, []any{expected}, stringsEqual)
	case "@match", "@search":
		return anyArrayItemMatches(value, []any{expected}, regexpMatches)
	case "@check", "@mirror":
		return anyTargetValueMatches(value, []any{expected}, stringsEqual)
	case "@regExp", "@checkRegExp":
		return anyTargetValueMatches(value, []any{expected}, regexpMatches)
	case "@startsWith":
		return anyTargetValueMatches(value, []any{expected}, strings.HasPrefix)
	case "@endsWith":
		return anyTargetValueMatches(value, []any{expected}, strings.HasSuffix)
	case "@equal":
		return anyTargetValueMatches(value, []any{expected}, numbersEqual)
	case "@greaterThan", "@lessThan", "@greaterThanOrEqual", "@lessThanOrEqual":
		return len(allExpected) > 0 && stringify(expected) == stringify(allExpected[0]) && Compare(comparator, value, allExpected)
	case "@inRange":
		return len(allExpected) >= 2 &&
			(stringify(expected) == stringify(allExpected[0]) || stringify(expected) == stringify(allExpected[1])) &&
			Compare(comparator, value, allExpected)
	default:
		return false
	}
}

func anyArrayItemMatches(value any, expected []any, matcher func(string, string) bool) bool {
	for _, item := range toAnySlice(value) {
		if anyExpectedMatches(item, expected, matcher) {
			return true
		}
	}
	return false
}

func anyExpectedMatches(value any, expected []any, matcher func(string, string) bool) bool {
	return anyTargetValueMatches(value, expected, matcher)
}

func anyTargetValueMatches(value any, expected []any, matcher func(string, string) bool) bool {
	if len(expected) == 0 {
		return false
	}
	text := stringify(value)
	for _, item := range expected {
		if matcher(text, stringify(item)) {
			return true
		}
	}
	return false
}

func stringsEqual(value string, expected string) bool {
	return value == expected
}

func regexpMatches(value string, pattern string) bool {
	matched, err := regexp.MatchString(pattern, value)
	return err == nil && matched
}

func numbersEqual(value string, expected string) bool {
	number, ok := parseFloat(value)
	if !ok {
		return false
	}
	expectedNumber, ok := parseFloat(expected)
	return ok && number == expectedNumber
}

func compareFirstNumber(value any, expected []any, matcher func(float64, float64) bool) bool {
	if len(expected) == 0 {
		return false
	}
	number, ok := toFloat(value)
	if !ok {
		return false
	}
	limit, ok := toFloat(expected[0])
	return ok && matcher(number, limit)
}

func compareRangeNumber(value any, expected []any) bool {
	if len(expected) < 2 {
		return false
	}
	number, ok := toFloat(value)
	if !ok {
		return false
	}
	from, ok := toFloat(expected[0])
	if !ok {
		return false
	}
	to, ok := toFloat(expected[1])
	return ok && number >= from && number <= to
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

func toFloat(value any) (float64, bool) {
	switch typed := value.(type) {
	case int:
		return float64(typed), true
	case int64:
		return float64(typed), true
	case uint64:
		return float64(typed), true
	case float32:
		return float64(typed), true
	case float64:
		return typed, true
	case json.Number:
		number, err := typed.Float64()
		return number, err == nil
	default:
		return parseFloat(stringify(value))
	}
}

func parseFloat(value string) (float64, bool) {
	number, err := strconv.ParseFloat(strings.TrimSpace(value), 64)
	return number, err == nil
}

func toAnySlice(value any) []any {
	switch typed := value.(type) {
	case nil:
		return nil
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
