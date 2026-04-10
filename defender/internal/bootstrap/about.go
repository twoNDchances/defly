package bootstrap

import (
	"defly-defender/internal/config"
	"defly-defender/internal/environments"

	"github.com/spf13/viper"
)

func NewAbout() {
	if !environments.AboutBannerEnable.Value() {
		return
	}

	viper.SetConfigFile("configs/about.yaml")

	if err := viper.ReadInConfig(); err != nil {
		panic(err)
	}

	var about config.About
	if err := viper.Unmarshal(&about); err != nil {
		panic(err)
	}

	about.PrintBanner()
	about.PrintDetail()
}
