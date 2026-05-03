package waf

import (
	"defly-defender/ent"
	targetruntime "defly-defender/internal/waf/principles/rules/targets"
	wordlistruntime "defly-defender/internal/waf/wordlist"
)

type Targets struct {
	core Core
}

func (t Targets) Extract(tx *Transaction, target *ent.Target, phase int) any {
	return targetruntime.Extractor{Wordlist: wordlistruntime.Loader{}}.Extract(tx, target, phase)
}

func (t Targets) WordlistWords(wordlist *ent.Wordlist) []string {
	return wordlistruntime.Loader{}.Words(wordlist)
}
