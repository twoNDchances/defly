package wordlist

import (
	"bufio"
	"log"
	"os"
	"path/filepath"

	"defly-defender/ent"
	entwordlist "defly-defender/ent/wordlist"
)

type Loader struct{}

func (Loader) Words(wordlist *ent.Wordlist) []string {
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
	file, err := os.Open(resolvePath(*wordlist.WordFile))
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

func resolvePath(path string) string {
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
