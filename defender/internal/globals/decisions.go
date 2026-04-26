package globals

type Decision struct {
	Id             string          `yaml:"id"`
	Name           string          `yaml:"name"`
	Direction      string          `yaml:"direction"`
	Condition      string          `yaml:"condition"`
	Score          float64         `yaml:"score"`
	Action         string          `yaml:"action"`
	Configurations *map[string]any `yaml:"configurations,omitempty"`
}
