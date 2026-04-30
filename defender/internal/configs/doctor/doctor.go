package doctor

import (
	"bytes"
	"context"
	"encoding/json"
	"fmt"
	"log"
	"runtime"
	"time"

	entdefender "defly-defender/ent/defender"
	"defly-defender/internal/configs"
	"defly-defender/internal/globals"
	"defly-defender/internal/utilities"
)

type Doctor struct {
	Defender configs.Defender
	Interval Interval
	Abnormal Abnormal
	Database configs.Database
	Error    configs.Error
}

func (d Doctor) Boot() error {
	errorFile, err := d.Error.Boot()
	if err != nil {
		return d.Error.LogError(err)
	}
	if errorFile != nil {
		defer errorFile.Close()
	}

	duration, err := d.Interval.Parse()
	if err != nil {
		return d.Error.LogError(err)
	}
	if duration <= 0 {
		return d.Error.LogString("doctor interval must be greater than zero")
	}

	time.Sleep(duration)

	status, details := d.CheckHealth()

	statusChanged, detailsChanged, err := d.shouldUpdate(status, details)
	if err != nil {
		return d.Error.LogError(err)
	}
	if !statusChanged && !detailsChanged {
		log.Println(utilities.Infof(
			"doctor checked defender %q: no changes (status=%s)",
			d.Defender.Name,
			status,
		))
		return nil
	}

	if err := d.updateDefenderHealth(status, details, statusChanged, statusChanged || detailsChanged); err != nil {
		return d.Error.LogError(err)
	}

	globals.Pauser.Lock()
	if globals.Defender != nil {
		if statusChanged {
			updatedStatus := status
			globals.Defender.Status = &updatedStatus
		}
		if statusChanged || detailsChanged {
			globals.Defender.Details = details
		}
	}
	globals.Pauser.Unlock()

	log.Println(utilities.Infof(
		"doctor checked defender %q: status=%s updated_status=%t updated_details=%t cpu=%v goroutines=%v alloc_mib=%v sys_mib=%v",
		d.Defender.Name,
		status,
		statusChanged,
		detailsChanged || statusChanged,
		details["cpu_cores"],
		details["goroutines"],
		details["process_memory_alloc_mib"],
		details["process_memory_sys_mib"],
	))

	return nil
}

func (d Doctor) CheckHealth() (entdefender.Status, map[string]any) {
	var memory runtime.MemStats
	runtime.ReadMemStats(&memory)

	cpuCores := runtime.NumCPU()
	goroutines := runtime.NumGoroutine()
	allocMiB := d.bytesToMiB(memory.Alloc)
	sysMiB := d.bytesToMiB(memory.Sys)
	memoryThresholdMiB := d.memoryThresholdMiB()
	goroutineThreshold := d.goroutineThreshold()

	reasons := []string{}
	status := entdefender.StatusNormal

	if cpuCores <= 0 {
		status = entdefender.StatusAbnormal
		reasons = append(reasons, "cpu core count is unavailable")
	}
	if sysMiB >= uint64(memoryThresholdMiB) {
		status = entdefender.StatusAbnormal
		reasons = append(reasons, fmt.Sprintf("process sys memory is high (%d MiB)", sysMiB))
	}
	if goroutines >= goroutineThreshold {
		status = entdefender.StatusAbnormal
		reasons = append(reasons, fmt.Sprintf("goroutine count is high (%d)", goroutines))
	}

	if len(reasons) == 0 {
		reasons = append(reasons, "all metrics are within default thresholds")
	}

	return status, map[string]any{
		"cpu_cores":                 cpuCores,
		"goroutines":                goroutines,
		"process_memory_alloc_mib":  allocMiB,
		"process_memory_sys_mib":    sysMiB,
		"memory_sys_threshold_mib":  memoryThresholdMiB,
		"goroutine_threshold_count": goroutineThreshold,
		"reasons":                   reasons,
	}
}

func (d Doctor) updateDefenderHealth(
	status entdefender.Status,
	details map[string]any,
	updateStatus bool,
	updateDetails bool,
) error {
	if !updateStatus && !updateDetails {
		return nil
	}

	client, err := d.Database.Connect()
	if err != nil {
		return fmt.Errorf("failed to connect database for doctor update: %w", err)
	}
	defer client.Close()

	entity, err := client.Defender.Query().
		Where(entdefender.NameEQ(d.Defender.Name)).
		Only(context.Background())
	if err != nil {
		return fmt.Errorf("failed to query defender %q for health update: %w", d.Defender.Name, err)
	}

	builder := client.Defender.UpdateOneID(entity.ID)
	if updateStatus {
		builder.SetStatus(status)
	}
	if updateDetails {
		builder.SetDetails(details)
	}

	if err := builder.Exec(context.Background()); err != nil {
		return fmt.Errorf("failed to update defender %q health status: %w", d.Defender.Name, err)
	}

	return nil
}

func (d Doctor) shouldUpdate(
	status entdefender.Status,
	details map[string]any,
) (bool, bool, error) {
	globals.Pauser.RLock()
	defer globals.Pauser.RUnlock()

	if globals.Defender == nil {
		return false, false, fmt.Errorf("global defender is not loaded")
	}

	statusChanged := globals.Defender.Status == nil || *globals.Defender.Status != status
	detailsChanged := !d.detailsEqual(globals.Defender.Details, details)

	return statusChanged, detailsChanged, nil
}

func (d Doctor) memoryThresholdMiB() int {
	if d.Abnormal.MemorySysThresholdMiB > 0 {
		return d.Abnormal.MemorySysThresholdMiB
	}
	return 1024
}

func (d Doctor) goroutineThreshold() int {
	if d.Abnormal.GoroutineThreshold > 0 {
		return d.Abnormal.GoroutineThreshold
	}
	return 10000
}

func (d Doctor) bytesToMiB(bytes uint64) uint64 {
	return bytes / 1024 / 1024
}

func (d Doctor) detailsEqual(left map[string]any, right map[string]any) bool {
	leftBytes, leftErr := json.Marshal(left)
	rightBytes, rightErr := json.Marshal(right)
	if leftErr != nil || rightErr != nil {
		return false
	}
	return bytes.Equal(leftBytes, rightBytes)
}
