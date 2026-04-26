package controllers

import (
	"strings"

	"defly-defender/internal/globals"

	"github.com/gin-gonic/gin"
)

type Policy struct {
	Policies *[]globals.Policy
	Store    Store[globals.Policy]
}

func (p *Policy) Apply(ctx *gin.Context) {
	var policies []globals.Policy
	if err := ctx.ShouldBind(&policies); err != nil {
		ctx.JSON(400, gin.H{
			"error": err.Error(),
		})
		return
	}

	if len(policies) == 0 {
		ctx.JSON(400, gin.H{
			"error": "policies are required",
		})
		return
	}

	for index := range policies {
		policies[index].Id = strings.TrimSpace(policies[index].Id)
		if policies[index].Id == "" {
			ctx.JSON(400, gin.H{
				"error": "policy id is required",
			})
			return
		}
	}

	if p.Store == nil {
		ctx.JSON(500, gin.H{
			"error": "policy storage is not configured",
		})
		return
	}

	if err := p.Store.Set(policies); err != nil {
		ctx.JSON(500, gin.H{
			"error": err.Error(),
		})
		return
	}

	ctx.JSON(200, gin.H{
		"policies": policies,
	})
}

func (p *Policy) Revoke(ctx *gin.Context) {
	var ids []string
	if err := ctx.ShouldBind(&ids); err != nil {
		ctx.JSON(400, gin.H{
			"error": err.Error(),
		})
		return
	}

	if len(ids) == 0 {
		ctx.JSON(400, gin.H{
			"error": "policy ids are required",
		})
		return
	}

	for index := range ids {
		ids[index] = strings.TrimSpace(ids[index])
		if ids[index] == "" {
			ctx.JSON(400, gin.H{
				"error": "policy id is required",
			})
			return
		}
	}

	if p.Store == nil {
		ctx.JSON(500, gin.H{
			"error": "policy storage is not configured",
		})
		return
	}

	revoked, err := p.Store.Unset(ids)
	if err != nil {
		ctx.JSON(500, gin.H{
			"error": err.Error(),
		})
		return
	}
	if len(revoked) == 0 {
		ctx.JSON(404, gin.H{
			"error": "policy not found",
		})
		return
	}

	ctx.JSON(200, gin.H{
		"ids":     revoked,
		"missing": missingIds(ids, revoked),
	})
}

func missingIds(ids, found []string) []string {
	foundById := map[string]bool{}
	for _, id := range found {
		foundById[id] = true
	}

	missing := []string{}
	for _, id := range ids {
		if !foundById[id] {
			missing = append(missing, id)
		}
	}

	return missing
}
