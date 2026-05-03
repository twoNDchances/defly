package engines

import "math"

type Addition struct {
	Digit float64
}

func (a Addition) Transform(value any) any {
	return toFloat(value) + a.Digit
}

type Subtraction struct {
	Digit float64
}

func (s Subtraction) Transform(value any) any {
	return toFloat(value) - s.Digit
}

type Multiplication struct {
	Digit float64
}

func (m Multiplication) Transform(value any) any {
	return toFloat(value) * m.Digit
}

type Division struct {
	Digit float64
}

func (d Division) Transform(value any) any {
	if d.Digit == 0 {
		return nil
	}
	return toFloat(value) / d.Digit
}

type PowerOf struct {
	Digit float64
}

func (p PowerOf) Transform(value any) any {
	return math.Pow(toFloat(value), p.Digit)
}

type Remainder struct {
	Digit float64
}

func (r Remainder) Transform(value any) any {
	if r.Digit == 0 {
		return nil
	}
	return math.Mod(toFloat(value), r.Digit)
}
