package server

import (
	"bytes"
	"os"

	"defly-defender/internal/globals"
	"defly-defender/internal/utilities"
	"go.yaml.in/yaml/v3"
)

type Storage struct {
	Type string
	Path string
	Data *Data
}

func (s *Storage) Load() error {
	if s.Data == nil {
		s.Data = &Data{}
	}

	if s.Type == "file" {
		file, err := utilities.CreateFileIfNotExists(s.Path)
		if err != nil {
			return err
		}
		if err := file.Close(); err != nil {
			return err
		}

		raw, err := os.ReadFile(s.Path)
		if err != nil {
			return err
		}

		if len(bytes.TrimSpace(raw)) > 0 {
			if err := yaml.Unmarshal(raw, s.Data); err != nil {
				return err
			}
		}
	}
	if s.Data.Policies == nil {
		s.Data.Policies = []globals.Policy{}
	}
	if s.Data.Decisions == nil {
		s.Data.Decisions = []globals.Decision{}
	}

	globals.Pauser.Lock()
	defer globals.Pauser.Unlock()

	globals.Policies = &s.Data.Policies
	globals.Decisions = &s.Data.Decisions

	return nil
}

func (s *Storage) persist() error {
	if s.Type != "file" {
		return nil
	}
	if s.Data == nil {
		s.Data = &Data{}
	}
	if globals.Policies != nil {
		s.Data.Policies = *globals.Policies
	}
	if globals.Decisions != nil {
		s.Data.Decisions = *globals.Decisions
	}

	raw, err := yaml.Marshal(s.Data)
	if err != nil {
		return err
	}
	return os.WriteFile(s.Path, raw, 0o666)
}
