package doctor

import "github.com/dogmatiq/ferrite"

var (
	DoctorIntervalUnit = ferrite.Enum("DOCTOR_INTERVAL_UNIT", "Time unit used for Doctor health-check interval").
				WithMembers("second", "minute", "hour").
				WithDefault("minute").
				Required()

	DoctorIntervalCount = ferrite.Signed[int]("DOCTOR_INTERVAL_COUNT", "Number of time units used for Doctor health-check interval").
				WithMinimum(1).
				WithMaximum(1000000).
				WithDefault(1).
				Required()
)
