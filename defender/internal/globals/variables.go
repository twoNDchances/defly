package globals

import "sync"

var (
	Pauser = &sync.RWMutex{}
)
