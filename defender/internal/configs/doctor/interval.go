package doctor

import (
	"fmt"
	"strings"
	"time"
)

type Interval struct {
	Unit  string
	Count int
}

func (i Interval) Parse() (time.Duration, error) {
	unit := strings.ToLower(strings.TrimSpace(i.Unit))
	if i.Count < 1 {
		return 0, fmt.Errorf("DOCTOR_INTERVAL_COUNT must be at least 1")
	}

	switch unit {
	case "second":
		if i.Count < 30 {
			return 0, fmt.Errorf("DOCTOR_INTERVAL_COUNT must be at least 30 when DOCTOR_INTERVAL_UNIT=second")
		}
		return time.Duration(i.Count) * time.Second, nil
	case "minute":
		return time.Duration(i.Count) * time.Minute, nil
	case "hour":
		return time.Duration(i.Count) * time.Hour, nil
	default:
		return 0, fmt.Errorf("unsupported DOCTOR_INTERVAL_UNIT: %s", i.Unit)
	}
}
