package waf

import (
	"regexp"
	"slices"
	"strings"

	"defly-defender/ent"
)

type Rules struct {
	core Core
}

func (r Rules) Match(tx *Transaction, rule *ent.Rule, phase int) bool {
	target := rule.Edges.Target
	value := any(nil)
	if target != nil {
		value = r.core.Targets().Extract(tx, target, phase)
		value = r.core.Engine().TransformTarget(value, target)
	}
	return r.compare(rule.Comparator, value, r.ruleExpectedValues(rule))
}

func (r Rules) ruleExpectedValues(rule *ent.Rule) []any {
	expected := make([]any, 0)
	for _, key := range []string{"number", "number_from", "number_to", "string", "value", "expected", "needle", "pattern", "min", "max"} {
		if value, ok := rule.Configurations[key]; ok {
			expected = append(expected, value)
		}
	}
	if wordlist := rule.Edges.Wordlist; wordlist != nil {
		for _, word := range r.core.Targets().WordlistWords(wordlist) {
			expected = append(expected, word)
		}
	}
	return expected
}

func (r Rules) compare(comparator string, value any, expected []any) bool {
	switch comparator {
	case "@check":
		return value != nil && r.core.stringify(value) != ""
	case "@equal":
		return slices.ContainsFunc(expected, func(item any) bool { return r.core.stringify(value) == r.core.stringify(item) })
	case "@contains", "@search":
		text := r.core.stringify(value)
		return slices.ContainsFunc(expected, func(item any) bool { return strings.Contains(text, r.core.stringify(item)) })
	case "@startsWith":
		text := r.core.stringify(value)
		return slices.ContainsFunc(expected, func(item any) bool { return strings.HasPrefix(text, r.core.stringify(item)) })
	case "@endsWith":
		text := r.core.stringify(value)
		return slices.ContainsFunc(expected, func(item any) bool { return strings.HasSuffix(text, r.core.stringify(item)) })
	case "@greaterThan":
		return r.core.toFloat(value) > r.core.firstFloat(expected)
	case "@lessThan":
		return r.core.toFloat(value) < r.core.firstFloat(expected)
	case "@greaterThanOrEqual":
		return r.core.toFloat(value) >= r.core.firstFloat(expected)
	case "@lessThanOrEqual":
		return r.core.toFloat(value) <= r.core.firstFloat(expected)
	case "@inRange":
		if len(expected) < 2 {
			return false
		}
		number := r.core.toFloat(value)
		return number >= r.core.toFloat(expected[0]) && number <= r.core.toFloat(expected[1])
	case "@match", "@regExp", "@checkRegExp":
		text := r.core.stringify(value)
		return slices.ContainsFunc(expected, func(item any) bool {
			matched, _ := regexp.MatchString(r.core.stringify(item), text)
			return matched
		})
	case "@mirror":
		items := r.core.toStrings(value)
		return len(items) > 0 && slices.ContainsFunc(items, func(item string) bool { return item == r.core.stringify(r.core.expectedValue(expected)) })
	case "@similar":
		return r.core.similarity(r.core.stringify(value), r.core.stringify(r.core.expectedValue(expected))) >= 0.8
	default:
		return false
	}
}
