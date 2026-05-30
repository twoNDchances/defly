package targets

import (
	"net/http"
	"net/url"
	"strings"

	"defly-defender/ent"
	enttarget "defly-defender/ent/target"
)

const (
	phaseRequestBody  = 3
	phaseResponseBody = 5
)

type Context interface {
	RawRequest() []byte
	RawResponse() []byte
	RequestBodyBytes() []byte
	ResponseBodyBytes() []byte
	RequestHeaders() http.Header
	ResponseHeaders() http.Header
	RequestQuery() url.Values
	RequestMethod() string
	RequestProto() string
	RequestURL() string
	RequestRemoteAddr() string
	RequestPath() string
	RequestScheme() string
	RequestHost() string
	RequestPort() float64
	RequestContentLength() int64
	RequestContentType() string
	ResponseStatusCode() int
	ResponseProto() string
	ResponseContentLength() int64
	ResponseContentType() string
	VarValue(key string) (any, bool)
}

type WordlistLoader interface {
	Words(wordlist *ent.Wordlist) []string
}

type Extractor struct {
	Wordlist WordlistLoader
}

func (e Extractor) Extract(tx Context, target *ent.Target, phase int) any {
	if tx == nil || target == nil || target.Phase != phase {
		return nil
	}
	if target.Type == enttarget.TypeFull && target.Edges.Pattern == nil {
		return nil
	}
	sourceType := target.Type
	if target.Edges.Pattern != nil {
		return ExtractPattern(target.Edges.Pattern.Name, tx)
	}

	if target.Datatype == enttarget.DatatypeArray && target.Edges.Wordlist != nil {
		words := e.wordlistWords(target.Edges.Wordlist)
		values := make([]string, 0, len(words))
		for _, key := range words {
			values = append(values, stringify(e.lookup(tx, phase, sourceType, key)))
		}
		return values
	}

	if sourceType == enttarget.TypeGetter {
		if value, ok := tx.VarValue(target.Name); ok {
			return value
		}
	}
	return e.lookup(tx, phase, sourceType, target.Name)
}

func (e Extractor) wordlistWords(wordlist *ent.Wordlist) []string {
	if e.Wordlist != nil {
		return e.Wordlist.Words(wordlist)
	}
	words := make([]string, 0, len(wordlist.WordJSON))
	for _, item := range wordlist.WordJSON {
		words = append(words, item.Word)
	}
	return words
}

func (e Extractor) lookup(tx Context, phase int, sourceType enttarget.Type, key string) any {
	switch sourceType {
	case enttarget.TypeFull:
		if phase <= phaseRequestBody {
			return string(tx.RawRequest())
		}
		return string(tx.RawResponse())
	case enttarget.TypeHeader:
		if phase <= phaseRequestBody {
			return headerValue(tx.RequestHeaders(), key)
		}
		return headerValue(tx.ResponseHeaders(), key)
	case enttarget.TypeMeta:
		return metaValue(tx, phase, key)
	case enttarget.TypeQuery:
		return tx.RequestQuery().Get(key)
	case enttarget.TypeBody:
		return bodyValue(bodyFields(tx, phase), key)
	case enttarget.TypeFile:
		return fileValue(fileFields(tx, phase), key)
	}
	return nil
}

func metaValue(tx Context, phase int, key string) any {
	if phase <= phaseRequestBody {
		switch strings.ToLower(key) {
		case "method":
			return tx.RequestMethod()
		case "protocol", "proto":
			return tx.RequestProto()
		case "path":
			return tx.RequestPath()
		case "url":
			return tx.RequestURL()
		case "host":
			return tx.RequestHost()
		case "scheme":
			return tx.RequestScheme()
		case "port":
			return tx.RequestPort()
		case "remote_addr", "ip":
			return tx.RequestRemoteAddr()
		case "content_length":
			return tx.RequestContentLength()
		}
	}
	switch strings.ToLower(key) {
	case "status", "status_code":
		return tx.ResponseStatusCode()
	case "protocol", "proto":
		return tx.ResponseProto()
	case "content_length":
		return tx.ResponseContentLength()
	}
	return nil
}

func bodyFields(tx Context, phase int) map[string]any {
	body := tx.RequestBodyBytes()
	contentType := tx.RequestContentType()
	if phase >= phaseResponseBody {
		body = tx.ResponseBodyBytes()
		contentType = tx.ResponseContentType()
	}
	fields, _ := parseBody(body, contentType)
	return fields
}

func fileFields(tx Context, phase int) map[string][]filePart {
	if phase != phaseRequestBody {
		return map[string][]filePart{}
	}
	_, files := parseBody(tx.RequestBodyBytes(), tx.RequestContentType())
	return files
}

func headerValue(headers http.Header, key string) any {
	if key == "" {
		return headers
	}
	values, ok := headers[http.CanonicalHeaderKey(key)]
	if !ok || len(values) == 0 {
		return nil
	}
	if len(values) == 1 {
		return values[0]
	}
	return values
}
