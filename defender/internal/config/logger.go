package config

import (
	"defly-defender/internal/utilities"
	"os"
	"strings"

	"github.com/gofiber/fiber/v3"
	"github.com/gofiber/fiber/v3/middleware/logger"
)

type Logger struct {
	From     string
	Format   string
	Timezone string
	File     bool
	Path     string
}

func (l Logger) Boot(application *fiber.App) *os.File {
	config := logger.Config{
		Format:     l.Format,
		TimeZone:   l.Timezone,
		TimeFormat: "02/01/2006 15:04:05",
		CustomTags: map[string]logger.LogFunc{
			"from": func(output logger.Buffer, c fiber.Ctx, data *logger.Data, extraParam string) (int, error) {
				return output.WriteString(strings.ToUpper(l.From))
			},
		},
		Stream: os.Stdout,
	}
	application.Use(logger.New(config))
	if l.File {
		file, _ := utilities.CreateFileIfNotExists(l.Path)
		config.Stream = file
		application.Use(logger.New(config))
		return file
	}
	return nil
}
