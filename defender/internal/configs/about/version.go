package about

type Version struct {
	Application string `mapstructure:"application"`
	Go          string `mapstructure:"go"`
	Gin         string `mapstructure:"gin"`
}
