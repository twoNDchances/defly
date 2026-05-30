package targets

import (
	"bytes"
	"encoding/json"
	"fmt"
	"io"
	"mime"
	"mime/multipart"
	"net/url"
	"path/filepath"
	"strconv"
	"strings"

	"github.com/gabriel-vasile/mimetype"
)

type filePart struct {
	Filename string
	Content  []byte
	Size     int64
}

func parseBody(body []byte, contentType string) (map[string]any, map[string][]filePart) {
	fields := make(map[string]any)
	files := make(map[string][]filePart)
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
			files[name] = append(files[name], filePart{
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

func bodyValue(body map[string]any, key string) any {
	if key == "" {
		return body
	}
	return dotted(body, key)
}

func fileValue(files map[string][]filePart, key string) any {
	parts, ok := files[key]
	if !ok || len(parts) == 0 {
		return nil
	}
	values := make([]string, 0, len(parts))
	for _, part := range parts {
		values = append(values, string(part.Content))
	}
	if len(values) == 1 {
		return values[0]
	}
	return values
}

func dotted(value any, key string) any {
	current := value
	for _, part := range strings.Split(key, ".") {
		switch typed := current.(type) {
		case map[string]any:
			current = typed[part]
		case []any:
			index, err := strconv.Atoi(part)
			if err != nil || index < 0 || index >= len(typed) {
				return nil
			}
			current = typed[index]
		default:
			return nil
		}
	}
	return current
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

func fileExtension(filename string) string {
	extension := strings.TrimPrefix(strings.ToLower(filepath.Ext(filename)), ".")
	return extension
}

func fileDetectedExtension(content []byte) string {
	return strings.TrimPrefix(strings.ToLower(mimetype.Detect(content).Extension()), ".")
}
