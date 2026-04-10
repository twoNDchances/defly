package utilities

import "strconv"

func StringToInteger(value string) int {
	integer, err := strconv.Atoi(value)
	if err != nil {
		return 0
	}
	return integer
}
