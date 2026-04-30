package configs

import (
	"defly-defender/internal/utilities"
	"fmt"
	"os"
	"path/filepath"
	"strings"
	"time"
)

type Error struct {
	From          string
	Label         string
	FileEnable    bool
	DirectoryPath string
	file          *os.File
}

func (e Error) fileName() string {
	label := strings.Map(func(r rune) rune {
		if r >= 'a' && r <= 'z' {
			return r
		}
		if r >= '0' && r <= '9' {
			return r
		}
		return '-'
	}, e.Label)
	return label + ".log"
}

func (e *Error) Boot() (*os.File, error) {
	if !e.FileEnable {
		return nil, nil
	}

	file, err := utilities.CreateFileIfNotExists(filepath.Join(e.DirectoryPath, e.fileName()))
	if err != nil {
		return nil, err
	}

	e.file = file
	return file, nil
}

func (e Error) save(message string) error {
	if !e.FileEnable {
		return nil
	}
	if e.file == nil {
		return fmt.Errorf("error log file is not initialized")
	}

	_, err := fmt.Fprintf(e.file,
		"%s {%s} [%s] %s\n",
		time.Now().Format(loggerTimeFormat),
		e.From,
		e.Label,
		message,
	)
	return err
}

func (e Error) Format(message string) string {
	return utilities.Dangerf("{%s} [%s] %s", e.From, e.Label, message)
}

func (e Error) LogString(message string) error {
	replacePair := []string{"\r\n", " ", "\n", " ", "\r", " "}
	message = strings.TrimSpace(strings.NewReplacer(replacePair...).Replace(message))
	if message == "" {
		return nil
	}

	formatted := e.Format(message)
	if e.FileEnable && e.file != nil {
		if err := e.save(message); err != nil {
			formatted = fmt.Sprintf("%s; %s", formatted, e.Format(fmt.Sprintf("failed to save error: %v", err)))
		}
	}

	return fmt.Errorf("%s", formatted)
}

func (e Error) LogError(err error) error {
	if err == nil {
		return nil
	}

	return e.LogString(err.Error())
}
