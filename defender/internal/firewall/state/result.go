package state

import "net/http"

type Result struct {
	Allow                 bool
	Deny                  bool
	Cancel                bool
	StopRequestDecisions  bool
	StopResponseDecisions bool
	Status                int
	ContentType           string
	Body                  []byte
	BodyRewrite           []byte
	BodyRewritten         bool
	RewriteHeaders        http.Header
	UnsetHeaders          []string
	RewritePath           string
	RewriteQuery          map[string]string
	UnsetQuery            []string
	RedirectURL           string
	ForceNoCache          bool
	EraseCookies          bool
}
