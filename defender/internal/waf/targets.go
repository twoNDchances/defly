package waf

import (
	"bufio"
	"log"
	"net/http"
	"os"
	"path/filepath"
	"strings"

	"defly-defender/ent"
	enttarget "defly-defender/ent/target"
	entwordlist "defly-defender/ent/wordlist"
)

type Targets struct {
	core Core
}

func (t Targets) Extract(tx *Transaction, target *ent.Target, phase int) any {
	if target == nil || target.Phase != phase {
		return nil
	}
	if (target.Type == enttarget.TypeFull || target.Type == enttarget.TypeMeta) && target.Edges.Pattern == nil {
		return nil
	}
	sourceType := target.Type
	if target.Edges.Pattern != nil {
		return t.lookupPattern(tx, target.Edges.Pattern.Name, phase)
	}

	if target.Datatype == enttarget.DatatypeArray && target.Edges.Wordlist != nil {
		words := t.WordlistWords(target.Edges.Wordlist)
		values := make([]string, 0, len(words))
		for _, key := range words {
			values = append(values, t.core.stringify(t.lookup(tx, phase, sourceType, key)))
		}
		return values
	}

	if sourceType == enttarget.TypeGetter {
		if value, ok := tx.Vars[target.Name]; ok {
			return value
		}
	}
	return t.lookup(tx, phase, sourceType, target.Name)
}

func (t Targets) lookup(tx *Transaction, phase int, sourceType enttarget.Type, key string) any {
	switch sourceType {
	case enttarget.TypeFull:
		if phase <= PhaseRequestBody {
			return string(tx.RequestRaw)
		}
		return string(tx.ResponseRaw)
	case enttarget.TypeHeader:
		if phase <= PhaseRequestBody && tx.Request != nil {
			return t.headerValue(tx.Request.Header, key)
		}
		if tx.Response != nil {
			return t.headerValue(tx.Response.Header, key)
		}
	case enttarget.TypeMeta:
		return t.metaValue(tx, phase, key)
	case enttarget.TypeQuery:
		if tx.Request != nil {
			return tx.Request.URL.Query().Get(key)
		}
	case enttarget.TypeBody:
		return t.core.bodyValue(t.bodyFields(tx, phase), key)
	case enttarget.TypeFile:
		return t.core.fileValue(t.fileFields(tx, phase), key)
	}
	return nil
}

func (t Targets) metaValue(tx *Transaction, phase int, key string) any {
	if phase <= PhaseRequestBody && tx.Request != nil {
		switch strings.ToLower(key) {
		case "method":
			return tx.Request.Method
		case "protocol", "proto":
			return tx.Request.Proto
		case "path":
			return tx.Request.URL.Path
		case "url":
			return tx.Request.URL.String()
		case "host":
			return tx.Request.Host
		case "scheme":
			if tx.Request.URL.Scheme != "" {
				return tx.Request.URL.Scheme
			}
			if tx.Request.TLS != nil {
				return "https"
			}
			return "http"
		case "port":
			return t.core.urlPort(tx.Request)
		case "remote_addr", "ip":
			return tx.Request.RemoteAddr
		case "content_length":
			return tx.Request.ContentLength
		}
	}
	if tx.Response != nil {
		switch strings.ToLower(key) {
		case "status", "status_code":
			return tx.Response.StatusCode
		case "protocol", "proto":
			return tx.Response.Proto
		case "content_length":
			return tx.Response.ContentLength
		}
	}
	return nil
}

func (t Targets) lookupPattern(tx *Transaction, name string, phase int) any {
	switch name {
	case "request-full":
		return string(tx.RequestRaw)
	case "response-full":
		return string(tx.ResponseRaw)
	case "request-full-headers":
		return t.core.headersString(tx.Request.Header)
	case "response-full-headers":
		if tx.Response != nil {
			return t.core.headersString(tx.Response.Header)
		}
	case "request-full-body":
		return string(tx.RequestBody)
	case "response-full-body":
		return string(tx.ResponseBody)
	case "request-header-keys":
		return t.core.headerKeys(tx.Request.Header)
	case "response-header-keys":
		return t.core.headerKeys(tx.Response.Header)
	case "request-header-values":
		return t.core.headerValues(tx.Request.Header)
	case "response-header-values":
		return t.core.headerValues(tx.Response.Header)
	case "request-header-size":
		return float64(len(tx.Request.Header))
	case "response-header-size":
		return float64(len(tx.Response.Header))
	case "request-query-keys":
		return t.core.queryKeys(tx.Request.URL.Query())
	case "request-query-values":
		return t.core.queryValues(tx.Request.URL.Query())
	case "request-query-size":
		return float64(len(tx.Request.URL.Query()))
	case "request-meta-url-port":
		return t.core.urlPort(tx.Request)
	case "request-meta-protocol":
		return tx.Request.Proto
	case "request-meta-ip":
		return tx.Request.RemoteAddr
	case "request-meta-method":
		return tx.Request.Method
	case "request-meta-url-path":
		return tx.Request.URL.Path
	case "request-meta-url-scheme":
		return t.core.stringify(t.metaValue(tx, phase, "scheme"))
	case "request-meta-url-host":
		return tx.Request.Host
	case "response-meta-status":
		return float64(tx.Response.StatusCode)
	case "response-meta-protocol":
		return tx.Response.Proto
	case "request-body-keys", "response-body-keys":
		return t.core.mapKeys(t.bodyFields(tx, phase))
	case "request-body-values", "response-body-values":
		return t.core.mapValues(t.bodyFields(tx, phase))
	case "request-body-size", "response-body-size":
		return float64(len(t.bodyFields(tx, phase)))
	case "request-body-length":
		return float64(len(tx.RequestBody))
	case "response-body-length":
		return float64(len(tx.ResponseBody))
	case "request-file-keys":
		return t.core.mapKeysFiles(t.fileFields(tx, phase))
	case "request-file-values":
		return t.core.fileContents(t.fileFields(tx, phase))
	case "request-file-names":
		return t.core.fileNames(t.fileFields(tx, phase))
	case "request-file-extensions":
		return t.core.fileExtensions(t.fileFields(tx, phase))
	case "request-file-size":
		return float64(len(t.fileFields(tx, phase)))
	case "request-file-name-size":
		return float64(len(t.core.fileNames(t.fileFields(tx, phase))))
	case "request-file-length":
		return t.core.fileLength(t.fileFields(tx, phase))
	}
	return nil
}

func (t Targets) bodyFields(tx *Transaction, phase int) map[string]any {
	body := tx.RequestBody
	contentType := ""
	if tx.Request != nil {
		contentType = tx.Request.Header.Get("Content-Type")
	}
	if phase >= PhaseResponseBody {
		body = tx.ResponseBody
		if tx.Response != nil {
			contentType = tx.Response.Header.Get("Content-Type")
		}
	}
	fields, _ := t.core.parseBody(body, contentType)
	return fields
}

func (t Targets) fileFields(tx *Transaction, phase int) map[string][]filePart {
	if phase != PhaseRequestBody || tx.Request == nil {
		return map[string][]filePart{}
	}
	_, files := t.core.parseBody(tx.RequestBody, tx.Request.Header.Get("Content-Type"))
	return files
}

func (t Targets) WordlistWords(wordlist *ent.Wordlist) []string {
	if wordlist == nil {
		return nil
	}
	if wordlist.Type == entwordlist.TypeJSON {
		words := make([]string, 0, len(wordlist.WordJSON))
		for _, item := range wordlist.WordJSON {
			words = append(words, item.Word)
		}
		return words
	}
	if wordlist.Type != entwordlist.TypeFile || wordlist.WordFile == nil || *wordlist.WordFile == "" {
		return nil
	}
	file, err := os.Open(t.resolveWordlistPath(*wordlist.WordFile))
	if err != nil {
		log.Println(err)
		return nil
	}
	defer file.Close()

	words := make([]string, 0)
	scanner := bufio.NewScanner(file)
	for scanner.Scan() {
		words = append(words, scanner.Text())
	}
	if err := scanner.Err(); err != nil {
		log.Println(err)
	}
	return words
}

func (t Targets) resolveWordlistPath(path string) string {
	if filepath.IsAbs(path) {
		return path
	}
	candidates := []string{
		path,
		filepath.Join("storage", "app", "public", path),
		filepath.Join("..", "manager", "storage", "app", "public", path),
		filepath.Join("..", "manager", "storage", "app", "private", path),
	}
	for _, candidate := range candidates {
		if _, err := os.Stat(candidate); err == nil {
			return candidate
		}
	}
	return path
}

func (t Targets) headerValue(headers http.Header, key string) any {
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
