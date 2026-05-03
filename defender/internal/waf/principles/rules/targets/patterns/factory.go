package patterns

func New(name string) Pattern {
	if pattern, ok := patternsByName[name]; ok {
		return pattern
	}
	return Noop{}
}

var (
	full   Full
	header Header
	query  Query
	meta   Meta
	body   Body
	file   File
)

var patternsByName = map[string]Pattern{
	"request-full":            pattern{extract: full.RequestFull},
	"response-full":           pattern{extract: full.ResponseFull},
	"request-full-headers":    pattern{extract: full.RequestFullHeaders},
	"response-full-headers":   pattern{extract: full.ResponseFullHeaders},
	"request-full-body":       pattern{extract: full.RequestFullBody},
	"response-full-body":      pattern{extract: full.ResponseFullBody},
	"request-header-keys":     pattern{extract: header.RequestHeaderKeys},
	"response-header-keys":    pattern{extract: header.ResponseHeaderKeys},
	"request-header-values":   pattern{extract: header.RequestHeaderValues},
	"response-header-values":  pattern{extract: header.ResponseHeaderValues},
	"request-header-size":     pattern{extract: header.RequestHeaderSize},
	"response-header-size":    pattern{extract: header.ResponseHeaderSize},
	"request-query-keys":      pattern{extract: query.RequestQueryKeys},
	"request-query-values":    pattern{extract: query.RequestQueryValues},
	"request-query-size":      pattern{extract: query.RequestQuerySize},
	"request-meta-url-port":   pattern{extract: meta.RequestMetaURLPort},
	"request-meta-protocol":   pattern{extract: meta.RequestMetaProtocol},
	"request-meta-ip":         pattern{extract: meta.RequestMetaIP},
	"request-meta-method":     pattern{extract: meta.RequestMetaMethod},
	"request-meta-url-path":   pattern{extract: meta.RequestMetaURLPath},
	"request-meta-url-scheme": pattern{extract: meta.RequestMetaURLScheme},
	"request-meta-url-host":   pattern{extract: meta.RequestMetaURLHost},
	"response-meta-status":    pattern{extract: meta.ResponseMetaStatus},
	"response-meta-protocol":  pattern{extract: meta.ResponseMetaProtocol},
	"request-body-keys":       pattern{extract: body.RequestBodyKeys},
	"response-body-keys":      pattern{extract: body.ResponseBodyKeys},
	"request-body-values":     pattern{extract: body.RequestBodyValues},
	"response-body-values":    pattern{extract: body.ResponseBodyValues},
	"request-body-size":       pattern{extract: body.RequestBodySize},
	"response-body-size":      pattern{extract: body.ResponseBodySize},
	"request-body-length":     pattern{extract: body.RequestBodyLength},
	"response-body-length":    pattern{extract: body.ResponseBodyLength},
	"request-file-keys":       pattern{extract: file.RequestFileKeys},
	"request-file-values":     pattern{extract: file.RequestFileValues},
	"request-file-names":      pattern{extract: file.RequestFileNames},
	"request-file-extensions": pattern{extract: file.RequestFileExtensions},
	"request-file-size":       pattern{extract: file.RequestFileSize},
	"request-file-name-size":  pattern{extract: file.RequestFileNameSize},
	"request-file-length":     pattern{extract: file.RequestFileLength},
}
