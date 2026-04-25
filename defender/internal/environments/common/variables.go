package common

import "github.com/dogmatiq/ferrite"

var (
	AboutBannerEnable = ferrite.Bool("ABOUT_BANNER_ENABLE", "Enable/Disable banner of Defly Defender when started").
				WithDefault(true).
				Required()

	ErrorFileEnable = ferrite.Bool("ERROR_FILE_ENABLE", "Enable/Disable saving Defly Defender errors to file").
			WithDefault(false).
			Required()

	ErrorDirectoryPath = ferrite.String("ERROR_DIRECTORY_PATH", "Directory path where Defly Defender errors are stored").
			WithDefault("resources/errors").
			WithConstraint("Must be a valid directory path", validateErrorDirectoryPath).
			Required(ferrite.RelevantIf(ErrorFileEnable))
)
