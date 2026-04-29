package configs

import (
	"bytes"
	"defly-defender/internal/utilities"
	"encoding/json"
	"fmt"
	"io"
	"net"
	"net/http"
	"net/url"
	"os"
	"strconv"
	"strings"
	"time"

	"github.com/gin-gonic/gin"
)

const (
	defaultLoggerFormat = "[%time%] {%from%}: %status% %ip% %method% %path% %bytesSent% %bytesReceived% %error%\n"
	loggerTimeFormat    = "02/01/2006 15:04:05"
)

var loggerColorTagValues = map[string]string{
	"black":   string(utilities.ColorBlack),
	"red":     string(utilities.ColorRed),
	"green":   string(utilities.ColorGreen),
	"yellow":  string(utilities.ColorYellow),
	"blue":    string(utilities.ColorBlue),
	"magenta": string(utilities.ColorMagenta),
	"cyan":    string(utilities.ColorCyan),
	"white":   string(utilities.ColorWhite),
	"reset":   string(utilities.ColorReset),
}

type Logger struct {
	From     string
	Format   string
	Timezone string
	File     bool
	Path     string
}

type loggerFormatFeatures struct {
	needsRequestBody  bool
	needsResponseBody bool
	needsFormValues   bool
}

type loggerFormatter struct {
	format   string
	features loggerFormatFeatures
}

type loggerRecord struct {
	location      *time.Location
	startedAt     time.Time
	finishedAt    time.Time
	from          string
	requestBody   []byte
	responseBody  string
	formValues    url.Values
	bytesSent     int
	bytesReceived int64
}

type responseBodyWriter struct {
	gin.ResponseWriter
	body *bytes.Buffer
}

func (writer *responseBodyWriter) Write(content []byte) (int, error) {
	if writer.body != nil {
		_, _ = writer.body.Write(content)
	}
	return writer.ResponseWriter.Write(content)
}

func (writer *responseBodyWriter) WriteString(content string) (int, error) {
	if writer.body != nil {
		_, _ = writer.body.WriteString(content)
	}
	return writer.ResponseWriter.WriteString(content)
}

func newLoggerFormatter(format string) loggerFormatter {
	if strings.TrimSpace(format) == "" {
		format = defaultLoggerFormat
	}

	lowerFormat := strings.ToLower(format)

	return loggerFormatter{
		format: format,
		features: loggerFormatFeatures{
			needsRequestBody:  strings.Contains(lowerFormat, "%body%") || strings.Contains(lowerFormat, "%form:"),
			needsResponseBody: strings.Contains(lowerFormat, "%resbody%"),
			needsFormValues:   strings.Contains(lowerFormat, "%form:"),
		},
	}
}

func (f loggerFormatter) needsRequestBody() bool  { return f.features.needsRequestBody }
func (f loggerFormatter) needsResponseBody() bool { return f.features.needsResponseBody }
func (f loggerFormatter) needsFormValues() bool   { return f.features.needsFormValues }

func (l Logger) Boot(application *gin.Engine) (*os.File, error) {
	location := time.Local
	if l.Timezone != "" {
		parsedLocation, err := time.LoadLocation(l.Timezone)
		if err == nil {
			location = parsedLocation
		}
	}

	writers := []io.Writer{os.Stdout}
	var file *os.File
	if l.File {
		createdFile, err := utilities.CreateFileIfNotExists(l.Path)
		if err != nil {
			return nil, fmt.Errorf("failed to open log file %s: %w", l.Path, err)
		}
		file = createdFile
		writers = append(writers, createdFile)
	}
	stream := io.MultiWriter(writers...)
	formatter := newLoggerFormatter(l.Format)

	application.Use(func(ctx *gin.Context) {
		startedAt := time.Now()
		requestBody := []byte(nil)
		formValues := url.Values{}
		if formatter.needsRequestBody() {
			requestBody = formatter.readAndRestoreRequestBody(ctx.Request)
			if formatter.needsFormValues() {
				formValues = formatter.parseRequestFormValues(ctx.Request, requestBody)
			}
		}

		var responseBodyBuffer bytes.Buffer
		if formatter.needsResponseBody() {
			ctx.Writer = &responseBodyWriter{ResponseWriter: ctx.Writer, body: &responseBodyBuffer}
		}

		ctx.Next()

		finishedAt := time.Now()
		bytesSent := ctx.Writer.Size()
		if bytesSent < 0 {
			bytesSent = 0
		}
		bytesReceived := ctx.Request.ContentLength
		if bytesReceived < 0 {
			bytesReceived = int64(len(requestBody))
		}

		record := loggerRecord{
			location:      location,
			startedAt:     startedAt,
			finishedAt:    finishedAt,
			from:          l.From,
			requestBody:   requestBody,
			responseBody:  responseBodyBuffer.String(),
			formValues:    formValues,
			bytesSent:     bytesSent,
			bytesReceived: bytesReceived,
		}

		logLine := formatter.render(ctx, record)
		if !strings.HasSuffix(logLine, "\n") {
			logLine += "\n"
		}
		_, _ = stream.Write([]byte(logLine))
	})

	return file, nil
}

func (f loggerFormatter) render(ctx *gin.Context, record loggerRecord) string {
	return f.renderFormat(func(tag string) (string, bool) { return f.resolveTag(tag, ctx, record) })
}

func (f loggerFormatter) renderFormat(resolver func(tag string) (string, bool)) string {
	if f.format == "" {
		return ""
	}
	var builder strings.Builder
	builder.Grow(len(f.format) + 64)
	for index := 0; index < len(f.format); {
		if f.format[index] != '%' {
			builder.WriteByte(f.format[index])
			index++
			continue
		}
		closingOffset := strings.IndexByte(f.format[index+1:], '%')
		if closingOffset < 0 {
			builder.WriteByte(f.format[index])
			index++
			continue
		}
		closingIndex := index + 1 + closingOffset
		tag := f.format[index+1 : closingIndex]
		if tag == "" {
			builder.WriteByte('%')
			index = closingIndex + 1
			continue
		}
		if value, ok := resolver(tag); ok {
			builder.WriteString(value)
		} else {
			builder.WriteString(f.format[index : closingIndex+1])
		}
		index = closingIndex + 1
	}
	return builder.String()
}

func (f loggerFormatter) resolveTag(tag string, ctx *gin.Context, record loggerRecord) (string, bool) {
	lowerTag := strings.ToLower(tag)
	if color, exists := loggerColorTagValues[lowerTag]; exists {
		return color, true
	}
	switch lowerTag {
	case "pid":
		return strconv.Itoa(os.Getpid()), true
	case "time":
		return record.startedAt.In(record.location).Format(loggerTimeFormat), true
	case "referer":
		return ctx.Request.Referer(), true
	case "protocol":
		return ctx.Request.Proto, true
	case "port":
		return f.extractPort(ctx.Request.RemoteAddr), true
	case "ip":
		return ctx.ClientIP(), true
	case "ips":
		ips := strings.TrimSpace(ctx.Request.Header.Get("X-Forwarded-For"))
		if ips == "" {
			return ctx.ClientIP(), true
		}
		return ips, true
	case "host":
		return ctx.Request.Host, true
	case "method":
		return ctx.Request.Method, true
	case "path":
		return ctx.Request.URL.Path, true
	case "url":
		return ctx.Request.URL.String(), true
	case "ua":
		return ctx.Request.UserAgent(), true
	case "latency":
		return record.finishedAt.Sub(record.startedAt).String(), true
	case "status":
		return strconv.Itoa(ctx.Writer.Status()), true
	case "resbody":
		return record.responseBody, true
	case "reqheaders":
		return f.serializeHeaders(ctx.Request.Header), true
	case "queryparams":
		return ctx.Request.URL.RawQuery, true
	case "body":
		return string(record.requestBody), true
	case "bytessent":
		return strconv.Itoa(record.bytesSent), true
	case "bytesreceived":
		return strconv.FormatInt(record.bytesReceived, 10), true
	case "route":
		route := ctx.FullPath()
		if route == "" {
			route = ctx.Request.URL.Path
		}
		return route, true
	case "error":
		return ctx.Errors.String(), true
	case "from":
		return strings.ToUpper(record.from), true
	}
	if parameter, ok := f.extractTagParameter(tag, lowerTag, "reqheader:"); ok {
		return ctx.GetHeader(parameter), true
	}
	if parameter, ok := f.extractTagParameter(tag, lowerTag, "respheader:"); ok {
		return ctx.Writer.Header().Get(parameter), true
	}
	if parameter, ok := f.extractTagParameter(tag, lowerTag, "query:"); ok {
		return ctx.Query(parameter), true
	}
	if parameter, ok := f.extractTagParameter(tag, lowerTag, "form:"); ok {
		if values := record.formValues[parameter]; len(values) > 0 {
			return values[0], true
		}
		return "", true
	}
	if parameter, ok := f.extractTagParameter(tag, lowerTag, "cookie:"); ok {
		cookieValue, err := ctx.Cookie(parameter)
		if err != nil {
			return "", true
		}
		return cookieValue, true
	}
	if parameter, ok := f.extractTagParameter(tag, lowerTag, "locals:"); ok {
		value, exists := ctx.Get(parameter)
		if !exists {
			return "", true
		}
		return fmt.Sprintf("%v", value), true
	}
	return "", false
}

func (f loggerFormatter) extractTagParameter(tag, lowerTag, prefix string) (string, bool) {
	if !strings.HasPrefix(lowerTag, prefix) {
		return "", false
	}
	separatorIndex := strings.Index(tag, ":")
	if separatorIndex < 0 || separatorIndex == len(tag)-1 {
		return "", true
	}
	return strings.TrimSpace(tag[separatorIndex+1:]), true
}

func (f loggerFormatter) extractPort(address string) string {
	_, port, err := net.SplitHostPort(strings.TrimSpace(address))
	if err != nil {
		return ""
	}
	return port
}

func (f loggerFormatter) serializeHeaders(header http.Header) string {
	if len(header) == 0 {
		return ""
	}
	serializedHeader, err := json.Marshal(header)
	if err != nil {
		return fmt.Sprintf("%v", header)
	}
	return string(serializedHeader)
}

func (f loggerFormatter) readAndRestoreRequestBody(request *http.Request) []byte {
	if request == nil || request.Body == nil {
		return nil
	}
	requestBody, err := io.ReadAll(request.Body)
	if err != nil {
		request.Body = io.NopCloser(bytes.NewBuffer(nil))
		return nil
	}
	_ = request.Body.Close()
	request.Body = io.NopCloser(bytes.NewBuffer(requestBody))
	return requestBody
}

func (f loggerFormatter) parseRequestFormValues(request *http.Request, requestBody []byte) url.Values {
	formValues := url.Values{}
	if request == nil {
		return formValues
	}
	requestCopy := request.Clone(request.Context())
	requestCopy.Body = io.NopCloser(bytes.NewBuffer(requestBody))
	requestCopy.ContentLength = int64(len(requestBody))
	contentType := strings.ToLower(requestCopy.Header.Get("Content-Type"))
	if strings.Contains(contentType, "multipart/form-data") {
		_ = requestCopy.ParseMultipartForm(32 << 20)
		if requestCopy.MultipartForm != nil {
			for key, values := range requestCopy.MultipartForm.Value {
				for _, value := range values {
					formValues.Add(key, value)
				}
			}
		}
	}
	_ = requestCopy.ParseForm()
	for key, values := range requestCopy.PostForm {
		for _, value := range values {
			formValues.Add(key, value)
		}
	}
	return formValues
}
