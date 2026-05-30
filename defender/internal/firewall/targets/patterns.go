package targets

import (
	"net/http"
	"strings"
)

func ExtractPattern(name string, ctx Context) any {
	switch name {
	case "request-full":
		return string(ctx.RawRequest())
	case "response-full":
		return string(ctx.RawResponse())
	case "request-full-headers":
		return serializeHeader(ctx.RequestHeaders())
	case "response-full-headers":
		return serializeHeader(ctx.ResponseHeaders())
	case "request-full-body":
		return string(ctx.RequestBodyBytes())
	case "response-full-body":
		return string(ctx.ResponseBodyBytes())
	case "request-header-keys":
		return headerKeys(ctx.RequestHeaders())
	case "response-header-keys":
		return headerKeys(ctx.ResponseHeaders())
	case "request-header-values":
		return headerValues(ctx.RequestHeaders())
	case "response-header-values":
		return headerValues(ctx.ResponseHeaders())
	case "request-header-size":
		return float64(len(ctx.RequestHeaders()))
	case "response-header-size":
		return float64(len(ctx.ResponseHeaders()))
	case "request-query-keys":
		return valueKeys(ctx.RequestQuery())
	case "request-query-values":
		return valueValues(ctx.RequestQuery())
	case "request-query-size":
		return float64(len(ctx.RequestQuery()))
	case "request-meta-url-port":
		return ctx.RequestPort()
	case "request-meta-protocol":
		return ctx.RequestProto()
	case "request-meta-ip":
		return normalizeIP(ctx.RequestRemoteAddr())
	case "request-meta-method":
		return ctx.RequestMethod()
	case "request-meta-url-path":
		return ctx.RequestPath()
	case "request-meta-url-scheme":
		return ctx.RequestScheme()
	case "request-meta-url-host":
		return ctx.RequestHost()
	case "response-meta-status":
		return float64(ctx.ResponseStatusCode())
	case "response-meta-protocol":
		return ctx.ResponseProto()
	case "request-body-keys":
		return mapKeys(bodyFields(ctx, phaseRequestBody))
	case "response-body-keys":
		return mapKeys(bodyFields(ctx, phaseResponseBody))
	case "request-body-values":
		return mapValues(bodyFields(ctx, phaseRequestBody))
	case "response-body-values":
		return mapValues(bodyFields(ctx, phaseResponseBody))
	case "request-body-size":
		return float64(len(bodyFields(ctx, phaseRequestBody)))
	case "request-body-length":
		return float64(len(ctx.RequestBodyBytes()))
	case "response-body-size":
		return float64(len(bodyFields(ctx, phaseResponseBody)))
	case "response-body-length":
		return float64(len(ctx.ResponseBodyBytes()))
	case "request-file-keys":
		return fileKeys(fileFields(ctx, phaseRequestBody))
	case "request-file-values":
		return fileValues(fileFields(ctx, phaseRequestBody))
	case "request-file-names":
		return fileNames(fileFields(ctx, phaseRequestBody))
	case "request-file-extensions":
		return fileExtensions(fileFields(ctx, phaseRequestBody))
	case "request-file-detected-extensions":
		return fileDetectedExtensions(fileFields(ctx, phaseRequestBody))
	case "request-file-size":
		return fileCount(fileFields(ctx, phaseRequestBody))
	case "request-file-length":
		return fileLength(fileFields(ctx, phaseRequestBody))
	case "request-file-name-size":
		return fileNameCount(fileFields(ctx, phaseRequestBody))
	default:
		return nil
	}
}

func serializeHeader(headers http.Header) string {
	var builder strings.Builder
	for key, values := range headers {
		for _, value := range values {
			builder.WriteString(key)
			builder.WriteString(": ")
			builder.WriteString(value)
			builder.WriteString("\r\n")
		}
	}
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
	for _, items := range headers {
		values = append(values, items...)
	}
	return values
}

type urlValues interface {
	Encode() string
}

func valueKeys(values map[string][]string) []string {
	keys := make([]string, 0, len(values))
	for key := range values {
		keys = append(keys, key)
	}
	return keys
}

func valueValues(values map[string][]string) []string {
	result := make([]string, 0)
	for _, items := range values {
		result = append(result, items...)
	}
	return result
}

func mapKeys(values map[string]any) []string {
	keys := make([]string, 0, len(values))
	for key := range values {
		keys = append(keys, key)
	}
	return keys
}

func mapValues(values map[string]any) []string {
	result := make([]string, 0, len(values))
	for _, value := range values {
		result = append(result, stringify(value))
	}
	return result
}

func fileKeys(files map[string][]filePart) []string {
	keys := make([]string, 0, len(files))
	for key := range files {
		keys = append(keys, key)
	}
	return keys
}

func fileValues(files map[string][]filePart) []string {
	values := make([]string, 0)
	for _, parts := range files {
		for _, part := range parts {
			values = append(values, string(part.Content))
		}
	}
	return values
}

func fileNames(files map[string][]filePart) []string {
	values := make([]string, 0)
	for _, parts := range files {
		for _, part := range parts {
			values = append(values, part.Filename)
		}
	}
	return values
}

func fileExtensions(files map[string][]filePart) []string {
	values := make([]string, 0)
	for _, parts := range files {
		for _, part := range parts {
			values = append(values, fileExtension(part.Filename))
		}
	}
	return values
}

func fileDetectedExtensions(files map[string][]filePart) []string {
	values := make([]string, 0)
	for _, parts := range files {
		for _, part := range parts {
			extension := fileDetectedExtension(part.Content)
			if extension == "" {
				continue
			}
			values = append(values, extension)
		}
	}
	return values
}

func fileCount(files map[string][]filePart) float64 {
	var count int
	for _, parts := range files {
		count += len(parts)
	}
	return float64(count)
}

func fileLength(files map[string][]filePart) float64 {
	var length int64
	for _, parts := range files {
		for _, part := range parts {
			length += part.Size
		}
	}
	return float64(length)
}

func fileNameCount(files map[string][]filePart) float64 {
	return float64(len(fileNames(files)))
}

func normalizeIP(value string) string {
	if value == "[::1]" || strings.HasPrefix(value, "[::1]:") {
		return "127.0.0.1"
	}
	return value
}
