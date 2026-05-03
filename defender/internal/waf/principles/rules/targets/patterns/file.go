package patterns

type File struct{}

func (File) RequestFileKeys(ctx Context) any {
	return fileKeys(requestFileFields(ctx))
}

func (File) RequestFileValues(ctx Context) any {
	return fileContents(requestFileFields(ctx))
}

func (File) RequestFileNames(ctx Context) any {
	return fileNames(requestFileFields(ctx))
}

func (File) RequestFileExtensions(ctx Context) any {
	return fileExtensions(requestFileFields(ctx))
}

func (File) RequestFileSize(ctx Context) any {
	return float64(len(requestFileFields(ctx)))
}

func (File) RequestFileNameSize(ctx Context) any {
	return float64(len(fileNames(requestFileFields(ctx))))
}

func (File) RequestFileLength(ctx Context) any {
	return fileLength(requestFileFields(ctx))
}
