package controllers

import (
	"net/http"

	"defly-defender/ent"
	entdefender "defly-defender/ent/defender"
	entprinciple "defly-defender/ent/principle"
	"defly-defender/internal/configs"
	"defly-defender/internal/globals"

	"github.com/gin-gonic/gin"
	"github.com/google/uuid"
)

type Principle struct {
	Database configs.Database
	Helper   Helper[principleRequest]
	Error    configs.Error
}

type principleRequest struct {
	PrincipleIDs []string `json:"principle_ids"`
}

func (p *Principle) Apply(ctx *gin.Context) {
	principles, principleIDsByPivotOrder, ok := p.findPrinciples(ctx)
	if !ok {
		return
	}

	globals.Pauser.Lock()
	defer globals.Pauser.Unlock()

	existing := make(map[uuid.UUID]bool, len(globals.Principles))
	for _, principle := range globals.Principles {
		if principle != nil {
			existing[principle.ID] = true
		}
	}

	added := 0
	for _, principle := range principles {
		if principle == nil || existing[principle.ID] {
			continue
		}
		globals.Principles = append(globals.Principles, principle)
		existing[principle.ID] = true
		added++
	}
	globals.Principles = p.Database.SortPrinciplesByPivotOrder(globals.Principles, principleIDsByPivotOrder)

	ctx.JSON(http.StatusOK, gin.H{
		"status":        "applied",
		"requested_ids": len(principles),
		"added":         added,
	})
}

func (p *Principle) Revoke(ctx *gin.Context) {
	principles, _, ok := p.findPrinciples(ctx)
	if !ok {
		return
	}

	revokeIDs := make(map[uuid.UUID]bool, len(principles))
	for _, principle := range principles {
		revokeIDs[principle.ID] = true
	}

	globals.Pauser.Lock()
	defer globals.Pauser.Unlock()

	next := globals.Principles[:0]
	removed := 0
	for _, principle := range globals.Principles {
		if principle != nil && revokeIDs[principle.ID] {
			removed++
			continue
		}
		next = append(next, principle)
	}
	globals.Principles = next

	ctx.JSON(http.StatusOK, gin.H{
		"status":        "revoked",
		"requested_ids": len(principles),
		"removed":       removed,
	})
}

func (p *Principle) findPrinciples(ctx *gin.Context) ([]*ent.Principle, []uuid.UUID, bool) {
	ids, ok := p.Helper.BindUUIDs(ctx, func(request principleRequest) []string {
		return request.PrincipleIDs
	})
	if !ok {
		return nil, nil, false
	}

	client, err := p.Database.Connect()
	if err != nil {
		_ = p.Error.LogError(err)
		ctx.JSON(http.StatusInternalServerError, gin.H{"error": "internal server error"})
		return nil, nil, false
	}
	defer client.Close()

	defender, err := client.Defender.Query().
		Where(entdefender.NameEQ(p.Database.Defender.Name)).
		Only(ctx.Request.Context())
	if err != nil {
		_ = p.Error.LogError(err)
		ctx.JSON(http.StatusInternalServerError, gin.H{"error": "internal server error"})
		return nil, nil, false
	}

	principles, err := defender.QueryPrinciples().
		Where(entprinciple.IDIn(ids...)).
		WithRules(func(ruleQuery *ent.RuleQuery) {
			ruleQuery.WithTarget(func(targetQuery *ent.TargetQuery) {
				targetQuery.WithPattern()
				targetQuery.WithWordlist()
				targetQuery.WithEngines()
			})
			ruleQuery.WithWordlist()
			ruleQuery.WithActions()
		}).
		All(ctx.Request.Context())
	if err != nil {
		_ = p.Error.LogError(err)
		ctx.JSON(http.StatusInternalServerError, gin.H{"error": "internal server error"})
		return nil, nil, false
	}
	principleIDsByPivotOrder, err := p.Database.PrincipleIDsByPivotOrder(ctx.Request.Context(), defender.ID, nil)
	if err != nil {
		_ = p.Error.LogError(err)
		ctx.JSON(http.StatusInternalServerError, gin.H{"error": "internal server error"})
		return nil, nil, false
	}
	principles = p.Database.SortPrinciplesByPivotOrder(principles, principleIDsByPivotOrder)

	existingIDs := make(map[uuid.UUID]bool, len(principles))
	for _, principle := range principles {
		if principle != nil {
			existingIDs[principle.ID] = true
		}
	}

	if missing := p.Helper.MissingUUIDs(ids, existingIDs); len(missing) > 0 {
		ctx.JSON(http.StatusNotFound, gin.H{
			"error":       "principle id does not exist",
			"missing_ids": missing,
		})
		return nil, nil, false
	}

	return principles, principleIDsByPivotOrder, true
}
