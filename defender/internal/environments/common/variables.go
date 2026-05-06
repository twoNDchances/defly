package common

import "github.com/dogmatiq/ferrite"

var (
	DefenderName = ferrite.String("DEFENDER_NAME", "Name used for Defly Defender generated files").
			WithDefault("defender").
			WithConstraint("Must be a valid file-safe Defender name", validateDefenderName).
			Required()

	AboutBannerEnable = ferrite.Bool("ABOUT_BANNER_ENABLE", "Enable/Disable banner of Defly Defender when started").
				WithDefault(true).
				Required()

	ErrorFileEnable = ferrite.Bool("ERROR_FILE_ENABLE", "Enable/Disable saving Defly Defender errors to file").
			WithDefault(false).
			Required()

	ErrorDirectoryPath = ferrite.String("ERROR_DIRECTORY_PATH", "Directory path where Defly Defender errors are stored").
				WithDefault("storage/errors").
				WithConstraint("Must be a valid directory path", validateErrorDirectoryPath).
				Required(ferrite.RelevantIf(ErrorFileEnable))

	WordlistRoot = ferrite.String("WORDLIST_ROOT", "Directory path where mounted wordlist files are stored").
			WithDefault("storage/wordlists").
			WithConstraint("Must be a valid directory path", validateWordlistRoot).
			Required()
)
