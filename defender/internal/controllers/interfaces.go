package controllers

type Store[T any] interface {
	Set(items []T) error
	Unset(ids []string) ([]string, error)
}
