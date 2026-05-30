package firewall

import "defly-defender/ent"

type Runtime struct {
	Principles        []*ent.Principle
	Decisions         []*ent.Decision
	ViolationScore    int
	ViolationLevel    int
	Severity          map[string]int
	ReportDatabaseDSN string
	ReportDefenderID  string
}

func (r Runtime) level() int {
	if r.ViolationLevel < 1 {
		return 1
	}
	return r.ViolationLevel
}
