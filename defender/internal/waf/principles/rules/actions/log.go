package actions

import "log"

type Log struct {
	Render func(tx Transaction) string
}

func (l Log) Execute(tx Transaction) {
	if l.Render == nil {
		return
	}
	log.Println(l.Render(tx))
}

func (Log) Async() bool {
	return false
}

func (Log) Validate() error {
	return nil
}
