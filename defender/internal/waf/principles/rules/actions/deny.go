package actions

import (
	"encoding/json"
	"errors"
	"fmt"
	"net/http"
	"slices"
)

type Deny struct {
	Status      int
	ContentType string
	Body        []byte
}

func (d Deny) Execute(tx Transaction) {
	tx.SetDeny(d.Status, d.ContentType, d.Body)
}

func (d Deny) Async() bool {
	return false
}

func (d Deny) Validate() error {
	errs := make([]error, 0)
	if http.StatusText(d.Status) == "" {
		errs = append(errs, fmt.Errorf("%d status is not valid", d.Status))
	}
	if !slices.Contains([]string{"application/json", "text/html; charset=utf-8"}, d.ContentType) {
		errs = append(errs, fmt.Errorf("%s content type is not valid", d.ContentType))
	}
	if d.ContentType == "application/json" && !json.Valid(d.Body) {
		errs = append(errs, fmt.Errorf("body is not valid for json content type"))
	}
	return errors.Join(errs...)
}
