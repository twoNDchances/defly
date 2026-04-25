package utilities

import (
	"os"
	"path/filepath"
	"strings"
)

func PathExists(path string) bool {
	_, err := os.Stat(path)
	return !os.IsNotExist(err)
}

func IsCreatableFilePath(path string) bool {
	path, ok := cleanPath(path)
	if !ok {
		return false
	}

	info, err := os.Stat(path)
	if err == nil {
		return !info.IsDir()
	}
	if !os.IsNotExist(err) {
		return false
	}

	return IsCreatableDirectoryPath(filepath.Dir(path))
}

func IsCreatableDirectoryPath(path string) bool {
	path, ok := cleanPath(path)
	if !ok {
		return false
	}

	info, err := os.Stat(path)
	if err == nil {
		return info.IsDir()
	}
	if !os.IsNotExist(err) {
		return false
	}

	parent := filepath.Dir(path)
	if parent == path {
		return false
	}

	return IsCreatableDirectoryPath(parent)
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

func cleanPath(path string) (string, bool) {
	path = strings.TrimSpace(path)
	if path == "" {
		return "", false
	}

	return filepath.Clean(path), true
}
