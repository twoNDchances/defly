package actions

import (
	"fmt"
	"log"
	"strings"

	"defly-defender/internal/utilities"
)

type Log struct {
	Render func(tx Transaction) string
	Path   string
}

func (l Log) Execute(tx Transaction) {
	if l.Render == nil {
		return
	}
	message := l.Render(tx)
	log.Println(message)
	if strings.TrimSpace(l.Path) == "" {
		return
	}
	file, err := utilities.CreateFileIfNotExists(l.Path)
	if err != nil {
		log.Println("waf log action could not open log file:", err)
		return
	}
	defer file.Close()
	if !strings.HasSuffix(message, "\n") {
		message += "\n"
	}
	if _, err := file.WriteString(message); err != nil {
		log.Println("waf log action could not write log file:", err)
	}
}

func (Log) Async() bool {
	return false
}

func (l Log) Validate() error {
	if strings.TrimSpace(l.Path) != "" && !utilities.IsCreatableFilePath(l.Path) {
		return fmt.Errorf("log file path is not writable: %s", l.Path)
	}
	return nil
}
