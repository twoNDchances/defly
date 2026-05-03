package actions

type Level struct {
	Value    float64
	Operator string
}

func (l Level) Execute(tx Transaction) {
	level := int(applyBehavior(float64(tx.CurrentLevel()), l.Value, l.Operator))
	if level < 1 {
		level = 1
	}
	tx.SetLevel(level)
}

func (Level) Async() bool {
	return false
}

func (Level) Validate() error {
	return nil
}
