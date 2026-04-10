package environments

import "github.com/dogmatiq/ferrite"

var AboutBannerEnable = ferrite.Bool("ABOUT_BANNER_ENABLE", "Enable/Disable banner of Defly Defender when started").WithDefault(true).Required()
