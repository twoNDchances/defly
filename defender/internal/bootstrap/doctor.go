package bootstrap

import (
	configdoctor "defly-defender/internal/configs/doctor"
	envdoctor "defly-defender/internal/environments/doctor"
)

func NewDoctor() error {
	from := "DOCTOR"
	runtimeError := NewError(from, "runtime")
	errorFile, err := runtimeError.Boot()
	if err != nil {
		return runtimeError.LogError(err)
	}
	if errorFile != nil {
		defer errorFile.Close()
	}

	doctor := configdoctor.Doctor{
		Defender: NewDefender(),
		Interval: configdoctor.Interval{
			Unit:  envdoctor.DoctorIntervalUnit.Value(),
			Count: envdoctor.DoctorIntervalCount.Value(),
		},
		Abnormal: configdoctor.Abnormal{
			MemorySysThresholdMiB: 1024,
			GoroutineThreshold:    10000,
		},
		Database: NewDatabase(),
		Error:    runtimeError,
	}
	if err := doctor.Boot(); err != nil {
		return runtimeError.LogError(err)
	}
	return nil
}
