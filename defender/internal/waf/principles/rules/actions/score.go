package actions

type Score struct {
	Value    float64
	Operator string
}

func (s Score) Execute(tx Transaction) {
	tx.SetScore(applyBehavior(tx.CurrentScore(), s.Value, s.Operator))
}

func (Score) Async() bool {
	return false
}

func (Score) Validate() error {
	return nil
}
