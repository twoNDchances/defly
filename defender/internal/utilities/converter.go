package utilities

import "strconv"

func StringToInteger(value string) int {
	integer, _ := strconv.Atoi(value)
	return integer
}
