package actions

type Suspect struct {
	Score float64
}

func (s Suspect) Execute(tx Transaction) {
	tx.AddScore(s.Score)
}

func (Suspect) Async() bool {
	return false
}

func (Suspect) Validate() error {
	return nil
}
