package server

import "defly-defender/internal/globals"

type Data struct {
	Policies  []globals.Policy   `yaml:"policies"`
	Decisions []globals.Decision `yaml:"decisions"`
}
