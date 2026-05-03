package actions

type SetterItem struct {
	Key   string
	Value any
}

type Setter struct {
	Directive string
	Items     []SetterItem
}

func (s Setter) Execute(tx Transaction) {
	if s.Directive == "unset" {
		for _, item := range s.Items {
			tx.UnsetVar(item.Key)
		}
		return
	}
	for _, item := range s.Items {
		tx.SetVar(item.Key, item.Value)
	}
}

func (Setter) Async() bool {
	return false
}

func (Setter) Validate() error {
	return nil
}
