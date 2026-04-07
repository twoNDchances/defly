package environments

import "github.com/dogmatiq/ferrite"

var BannerEnable = ferrite.Bool("BANNER_ENABLE", "Enable/Disable banner of Defly Defender when started").WithDefault(true).Required()
