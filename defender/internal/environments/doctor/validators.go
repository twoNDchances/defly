package doctor

import "fmt"

func ValidateDoctorInterval() error {
	unit := DoctorIntervalUnit.Value()
	count := DoctorIntervalCount.Value()
	return validateDoctorInterval(unit, count)
}

func validateDoctorInterval(unit string, count int) error {
	if unit == "second" && count < 30 {
		return fmt.Errorf("DOCTOR_INTERVAL_COUNT must be at least 30 when DOCTOR_INTERVAL_UNIT=second")
	}

	return nil
}
