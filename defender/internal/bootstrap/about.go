package bootstrap

import (
	"defly-defender/internal/config"
	envcommon "defly-defender/internal/environments/common"

	"github.com/spf13/viper"
)

func NewAbout() error {
	if !envcommon.AboutBannerEnable.Value() {
		return nil
	}

	viper.SetConfigFile("configs/about.yaml")

	if err := viper.ReadInConfig(); err != nil {
		return err
	}

	var about config.About
	if err := viper.Unmarshal(&about); err != nil {
		return err
	}

	about.PrintBanner()
	about.PrintDetail()
	return nil
}
