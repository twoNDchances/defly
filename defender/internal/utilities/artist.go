package utilities

import (
	"fmt"
	"os"
)

// ANSIColor represents an ANSI escape code for terminal text formatting.
type ANSIColor string

const (
	ColorReset ANSIColor = "\033[0m"

	ColorBlack   ANSIColor = "\033[30m"
	ColorRed     ANSIColor = "\033[31m"
	ColorGreen   ANSIColor = "\033[32m"
	ColorYellow  ANSIColor = "\033[33m"
	ColorBlue    ANSIColor = "\033[34m"
	ColorMagenta ANSIColor = "\033[35m"
	ColorCyan    ANSIColor = "\033[36m"
	ColorWhite   ANSIColor = "\033[37m"

	ColorBrightBlack   ANSIColor = "\033[90m"
	ColorBrightRed     ANSIColor = "\033[91m"
	ColorBrightGreen   ANSIColor = "\033[92m"
	ColorBrightYellow  ANSIColor = "\033[93m"
	ColorBrightBlue    ANSIColor = "\033[94m"
	ColorBrightMagenta ANSIColor = "\033[95m"
	ColorBrightCyan    ANSIColor = "\033[96m"
	ColorBrightWhite   ANSIColor = "\033[97m"
)

// EnableColor controls whether ANSI color escape codes are emitted.
// It is disabled when NO_COLOR exists in environment variables.
var EnableColor = os.Getenv("NO_COLOR") == ""

// Colorize wraps the text in ANSI color escape codes.
func Colorize(text string, color ANSIColor) string {
	if !EnableColor || color == "" {
		return text
	}
	return string(color) + text + string(ColorReset)
}

// Sprint formats text then wraps it with the provided color.
func Sprint(color ANSIColor, format string, args ...any) string {
	return Colorize(fmt.Sprintf(format, args...), color)
}

// Danger colorizes text for critical/error messages.
func Danger(text string) string {
	return Colorize(text, ColorBrightRed)
}

func Dangerf(format string, args ...any) string {
	return Sprint(ColorBrightRed, format, args...)
}

// Warning colorizes text for warning messages.
func Warning(text string) string {
	return Colorize(text, ColorBrightYellow)
}

func Warningf(format string, args ...any) string {
	return Sprint(ColorBrightYellow, format, args...)
}

// Success colorizes text for success messages.
func Success(text string) string {
	return Colorize(text, ColorBrightGreen)
}

func Successf(format string, args ...any) string {
	return Sprint(ColorBrightGreen, format, args...)
}

// Info colorizes text for informational messages.
func Info(text string) string {
	return Colorize(text, ColorBrightCyan)
}

func Infof(format string, args ...any) string {
	return Sprint(ColorBrightCyan, format, args...)
}

// Muted colorizes text for low-emphasis messages.
func Muted(text string) string {
	return Colorize(text, ColorBrightBlack)
}

func Mutedf(format string, args ...any) string {
	return Sprint(ColorBrightBlack, format, args...)
}
