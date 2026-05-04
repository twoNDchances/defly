package patterns

import (
	"bytes"
	"encoding/json"
	"fmt"
	"io"
	"mime"
	"mime/multipart"
	"net/http"
	"net/url"
	"path/filepath"
	"strings"

	"github.com/gabriel-vasile/mimetype"
)

type FilePart struct {
	Filename string
	Content  []byte
	Size     int64
}

func requestBodyFields(ctx Context) map[string]any {
	fields, _ := parseBody(ctx.RequestBodyBytes(), ctx.RequestContentType())
	return fields
}

func responseBodyFields(ctx Context) map[string]any {
	fields, _ := parseBody(ctx.ResponseBodyBytes(), ctx.ResponseContentType())
	return fields
}

func requestFileFields(ctx Context) map[string][]FilePart {
	_, files := parseBody(ctx.RequestBodyBytes(), ctx.RequestContentType())
	return files
}

func parseBody(body []byte, contentType string) (map[string]any, map[string][]FilePart) {
	fields := make(map[string]any)
	files := make(map[string][]FilePart)
	if len(body) == 0 {
		return fields, files
	}

	mediaType, params, _ := mime.ParseMediaType(contentType)
	switch mediaType {
	case "application/json":
		var decoded any
		if err := json.Unmarshal(body, &decoded); err == nil {
			if mapped, ok := decoded.(map[string]any); ok {
				return mapped, files
			}
			fields["body"] = decoded
		}
	case "application/x-www-form-urlencoded":
		values, err := url.ParseQuery(string(body))
		if err == nil {
			for key, value := range values {
				if len(value) == 1 {
					fields[key] = value[0]
				} else {
					fields[key] = value
				}
			}
		}
	case "multipart/form-data":
		reader := multipart.NewReader(bytes.NewReader(body), params["boundary"])
		for {
			part, err := reader.NextPart()
			if err == io.EOF {
				break
			}
			if err != nil {
				break
			}
			content, _ := io.ReadAll(part)
			name := part.FormName()
			if name == "" {
				continue
			}
			if part.FileName() == "" {
				fields[name] = string(content)
				continue
			}
			files[name] = append(files[name], FilePart{
				Filename: part.FileName(),
				Content:  content,
				Size:     int64(len(content)),
			})
		}
	default:
		fields["body"] = string(body)
	}
	return fields, files
}

func headersString(headers http.Header) string {
	var builder strings.Builder
	_ = headers.Write(&builder)
	return builder.String()
}

func headerKeys(headers http.Header) []string {
	keys := make([]string, 0, len(headers))
	for key := range headers {
		keys = append(keys, key)
	}
	return keys
}

func headerValues(headers http.Header) []string {
	values := make([]string, 0)
	for _, headerValues := range headers {
		values = append(values, headerValues...)
	}
	return values
}

func queryKeys(query url.Values) []string {
	keys := make([]string, 0, len(query))
	for key := range query {
		keys = append(keys, key)
	}
	return keys
}

func queryValues(query url.Values) []string {
	values := make([]string, 0)
	for _, queryValues := range query {
		values = append(values, queryValues...)
	}
	return values
}

func mapKeys(values map[string]any) []string {
	keys := make([]string, 0, len(values))
	for key := range values {
		keys = append(keys, key)
	}
	return keys
}

func mapValues(values map[string]any) []string {
	items := make([]string, 0, len(values))
	for _, value := range values {
		items = append(items, stringify(value))
	}
	return items
}

func fileKeys(values map[string][]FilePart) []string {
	keys := make([]string, 0, len(values))
	for key := range values {
		keys = append(keys, key)
	}
	return keys
}

func fileNames(values map[string][]FilePart) []string {
	names := make([]string, 0)
	for _, parts := range values {
		for _, part := range parts {
			names = append(names, part.Filename)
		}
	}
	return names
}

func fileExtensions(values map[string][]FilePart) []string {
	extensions := make([]string, 0)
	for _, name := range fileNames(values) {
		extensions = append(extensions, strings.TrimPrefix(filepath.Ext(name), "."))
	}
	return extensions
}

func fileDetectedExtensions(values map[string][]FilePart) []string {
	extensions := make([]string, 0)
	for _, parts := range values {
		for _, part := range parts {
			extension := strings.TrimPrefix(mimetype.Detect(part.Content).Extension(), ".")
			if extension == "" {
				continue
			}
			extensions = append(extensions, extension)
		}
	}
	return extensions
}

func fileContents(values map[string][]FilePart) []string {
	contents := make([]string, 0)
	for _, parts := range values {
		for _, part := range parts {
			contents = append(contents, string(part.Content))
		}
	}
	return contents
}

func fileLength(values map[string][]FilePart) float64 {
	var length int64
	for _, parts := range values {
		for _, part := range parts {
			length += part.Size
		}
	}
	return float64(length)
}

func stringify(value any) string {
	switch typed := value.(type) {
	case nil:
		return ""
	case string:
		return typed
	case []byte:
		return string(typed)
	case fmt.Stringer:
		return typed.String()
	default:
		return fmt.Sprint(typed)
	}
}
