package globals

import "sync"

var (
	Pauser = &sync.RWMutex{}
	Policies *[]Policy
	Decisions *[]Decision
)
