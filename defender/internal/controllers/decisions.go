package controllers

import (
	"net/http"

	"defly-defender/ent"
	entdecision "defly-defender/ent/decision"
	entdefender "defly-defender/ent/defender"
	"defly-defender/internal/configs"
	"defly-defender/internal/globals"

	"github.com/gin-gonic/gin"
	"github.com/google/uuid"
)

type Decision struct {
	Database configs.Database
	Helper   Helper[decisionRequest]
	Error    configs.Error
}

type decisionRequest struct {
	DecisionIDs []string `json:"decision_ids"`
}

func (d *Decision) Implement(ctx *gin.Context) {
	decisions, decisionIDsByPivotOrder, ok := d.findDecisions(ctx)
	if !ok {
		return
	}

	globals.Pauser.Lock()
	defer globals.Pauser.Unlock()

	existing := make(map[uuid.UUID]bool, len(globals.Decisions))
	for _, decision := range globals.Decisions {
		if decision != nil {
			existing[decision.ID] = true
		}
	}

	added := 0
	for _, decision := range decisions {
		if decision == nil || existing[decision.ID] {
			continue
		}
		globals.Decisions = append(globals.Decisions, decision)
		existing[decision.ID] = true
		added++
	}
	globals.Decisions = d.Database.SortDecisionsByPivotOrder(globals.Decisions, decisionIDsByPivotOrder)

	ctx.JSON(http.StatusOK, gin.H{
		"status":        "implemented",
		"requested_ids": len(decisions),
		"added":         added,
	})
}

func (d *Decision) Suspend(ctx *gin.Context) {
	decisions, _, ok := d.findDecisions(ctx)
	if !ok {
		return
	}

	suspendIDs := make(map[uuid.UUID]bool, len(decisions))
	for _, decision := range decisions {
		suspendIDs[decision.ID] = true
	}

	globals.Pauser.Lock()
	defer globals.Pauser.Unlock()

	next := globals.Decisions[:0]
	removed := 0
	for _, decision := range globals.Decisions {
		if decision != nil && suspendIDs[decision.ID] {
			removed++
			continue
		}
		next = append(next, decision)
	}
	globals.Decisions = next

	ctx.JSON(http.StatusOK, gin.H{
		"status":        "suspended",
		"requested_ids": len(decisions),
		"removed":       removed,
	})
}

func (d *Decision) findDecisions(ctx *gin.Context) ([]*ent.Decision, []uuid.UUID, bool) {
	ids, ok := d.Helper.BindUUIDs(ctx, func(request decisionRequest) []string {
		return request.DecisionIDs
	})
	if !ok {
		return nil, nil, false
	}

	client, err := d.Database.Connect()
	if err != nil {
		_ = d.Error.LogError(err)
		ctx.JSON(http.StatusInternalServerError, gin.H{"error": "internal server error"})
		return nil, nil, false
	}
	defer client.Close()

	defender, err := client.Defender.Query().
		Where(entdefender.NameEQ(d.Database.Defender.Name)).
		Only(ctx.Request.Context())
	if err != nil {
		_ = d.Error.LogError(err)
		ctx.JSON(http.StatusInternalServerError, gin.H{"error": "internal server error"})
		return nil, nil, false
	}

	decisions, err := defender.QueryDecisions().
		Where(entdecision.IDIn(ids...)).
		All(ctx.Request.Context())
	if err != nil {
		_ = d.Error.LogError(err)
		ctx.JSON(http.StatusInternalServerError, gin.H{"error": "internal server error"})
		return nil, nil, false
	}
	decisionIDsByPivotOrder, err := d.Database.DecisionIDsByPivotOrder(ctx.Request.Context(), defender.ID, nil)
	if err != nil {
		_ = d.Error.LogError(err)
		ctx.JSON(http.StatusInternalServerError, gin.H{"error": "internal server error"})
		return nil, nil, false
	}
	decisions = d.Database.SortDecisionsByPivotOrder(decisions, decisionIDsByPivotOrder)

	existingIDs := make(map[uuid.UUID]bool, len(decisions))
	for _, decision := range decisions {
		if decision != nil {
			existingIDs[decision.ID] = true
		}
	}

	if missing := d.Helper.MissingUUIDs(ids, existingIDs); len(missing) > 0 {
		ctx.JSON(http.StatusNotFound, gin.H{
			"error":       "decision id does not exist",
			"missing_ids": missing,
		})
		return nil, nil, false
	}

	return decisions, decisionIDsByPivotOrder, true
}
