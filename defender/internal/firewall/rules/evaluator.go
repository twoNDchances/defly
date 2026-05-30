package rules

import (
	"defly-defender/ent"
	"defly-defender/internal/firewall/targets"
)

type TargetExtractor interface {
	Extract(tx targets.Context, target *ent.Target, phase int) any
}

type TargetTransformer interface {
	TransformTarget(value any, target *ent.Target) any
}

type WordlistLoader interface {
	Words(wordlist *ent.Wordlist) []string
}

type Evaluator struct {
	Targets  TargetExtractor
	Engines  TargetTransformer
	Wordlist WordlistLoader
}

func (e Evaluator) Match(tx targets.Context, rule *ent.Rule, phase int) bool {
	if rule == nil {
		return false
	}
	target := rule.Edges.Target
	value := any(nil)
	if target != nil {
		value = e.Targets.Extract(tx, target, phase)
		value = e.Engines.TransformTarget(value, target)
	}
	return Compare(rule.Comparator, value, e.expectedValues(rule))
}

func (e Evaluator) expectedValues(rule *ent.Rule) []any {
	expected := make([]any, 0)
	for _, key := range []string{"number", "number_from", "number_to", "string", "value", "expected", "needle", "pattern", "min", "max"} {
		if value, ok := rule.Configurations[key]; ok {
			expected = append(expected, value)
		}
	}
	if wordlist := rule.Edges.Wordlist; wordlist != nil {
		for _, word := range e.wordlistWords(wordlist) {
			expected = append(expected, word)
		}
	}
	return expected
}

func (e Evaluator) wordlistWords(wordlist *ent.Wordlist) []string {
	if e.Wordlist != nil {
		return e.Wordlist.Words(wordlist)
	}
	words := make([]string, 0, len(wordlist.WordJSON))
	for _, item := range wordlist.WordJSON {
		words = append(words, item.Word)
	}
	return words
}
