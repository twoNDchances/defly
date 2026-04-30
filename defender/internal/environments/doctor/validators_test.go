package doctor

import "testing"

func TestValidateDoctorInterval(t *testing.T) {
	tests := map[string]struct {
		unit    string
		count   int
		wantErr bool
	}{
		"second with minimum count": {
			unit:    "second",
			count:   30,
			wantErr: false,
		},
		"second below minimum count": {
			unit:    "second",
			count:   29,
			wantErr: true,
		},
		"minute with small count": {
			unit:    "minute",
			count:   1,
			wantErr: false,
		},
		"hour with count": {
			unit:    "hour",
			count:   1,
			wantErr: false,
		},
	}

	for name, tt := range tests {
		t.Run(name, func(t *testing.T) {
			err := validateDoctorInterval(tt.unit, tt.count)
			if (err != nil) != tt.wantErr {
				t.Fatalf("ValidateDoctorInterval() error = %v, wantErr %t", err, tt.wantErr)
			}
		})
	}
}
