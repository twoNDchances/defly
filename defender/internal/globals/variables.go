package globals

import "sync"

var (
	Pauser    = &sync.RWMutex{}
	Gate      = &sync.RWMutex{}
	Policies  *[]Policy
	Decisions *[]Decision
)
