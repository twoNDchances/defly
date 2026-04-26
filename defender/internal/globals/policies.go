package globals

type Wordlist struct {
	Id    string   `yaml:"id"`
	Name  string   `yaml:"name"`
	Words []string `yaml:"words"`
}

type Engine struct {
	Id             string          `yaml:"id"`
	Name           string          `yaml:"name"`
	InputDatatype  string          `yaml:"input_datatype"`
	Type           string          `yaml:"type"`
	Configurations *map[string]any `yaml:"configurations,omitempty"`
	OutputDatatype string          `yaml:"output_datatype"`
}

type Pattern struct {
	Id       string `yaml:"id"`
	Name     string `yaml:"name"`
	Phase    int    `yaml:"phase"`
	Type     string `yaml:"type"`
	Datatype string `yaml:"datatype"`
}

type Target struct {
	Id       string    `yaml:"id"`
	Name     string    `yaml:"name"`
	Phase    int       `yaml:"phase"`
	Type     string    `yaml:"type"`
	Datatype string    `yaml:"datatype"`
	Pattern  *Pattern  `yaml:"pattern,omitempty"`
	Wordlist *Wordlist `yaml:"wordlist,omitempty"`
	Engines  []Engine  `yaml:"engines,omitempty"`
}

type Action struct {
	Id             string          `yaml:"id"`
	Name           string          `yaml:"name"`
	Type           string          `yaml:"type"`
	Configurations *map[string]any `yaml:"configurations,omitempty"`
}

type Rule struct {
	Id             string          `yaml:"id"`
	Name           string          `yaml:"name"`
	Phase          int             `yaml:"phase"`
	Target         *Target         `yaml:"target,omitempty"`
	Comparator     string          `yaml:"comparator"`
	IsInversed     bool            `yaml:"is_inversed"`
	Configurations *map[string]any `yaml:"configurations,omitempty"`
	Wordlist       *Wordlist       `yaml:"wordlist,omitempty"`
	Actions        []Action        `yaml:"actions,omitempty"`
}

type Policy struct {
	Id    string `yaml:"id"`
	Name  string `yaml:"name"`
	Level int    `yaml:"level"`
	Phase int    `yaml:"phase"`
	Rules []Rule `yaml:"rules,omitempty"`
}
