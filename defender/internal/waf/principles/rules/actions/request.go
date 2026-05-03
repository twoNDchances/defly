package actions

type Request struct {
	Send func()
}

func (r Request) Execute(tx Transaction) {
	if r.Send != nil {
		r.Send()
	}
}

func (Request) Async() bool {
	return true
}

func (Request) Validate() error {
	return nil
}
