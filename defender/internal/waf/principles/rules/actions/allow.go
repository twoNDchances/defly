package actions

type Allow struct{}

func (Allow) Execute(tx Transaction) {
	tx.SetAllow()
}

func (Allow) Async() bool {
	return false
}

func (Allow) Validate() error {
	return nil
}
