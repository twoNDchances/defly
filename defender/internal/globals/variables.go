package globals

import (
	"sync"

	"defly-defender/ent"
)

var (
	Pauser = &sync.RWMutex{}

	Defender   *ent.Defender
	Principles []*ent.Principle
	Decisions  []*ent.Decision
)
