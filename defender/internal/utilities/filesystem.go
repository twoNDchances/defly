package utilities

import (
	"os"
	"path/filepath"
)

func PathExists(path string) bool {
	_, err := os.Stat(path)
	return !os.IsNotExist(err)
}

func CreateFileIfNotExists(path string) (*os.File, error) {
	_, err := os.Stat(path)
	if err == nil {
		return os.OpenFile(path, os.O_RDWR|os.O_APPEND, 0666)
	}

	if !os.IsNotExist(err) {
		return nil, err
	}

	dir := filepath.Dir(path)
	if err := os.MkdirAll(dir, os.ModePerm); err != nil {
		return nil, err
	}

	file, err := os.Create(path)
	if err != nil {
		return nil, err
	}

	return file, nil
}
