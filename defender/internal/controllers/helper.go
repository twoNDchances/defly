package controllers

import (
	"fmt"
	"net/http"

	"github.com/gin-gonic/gin"
	"github.com/google/uuid"
)

type Helper[T any] struct{}

func (Helper[T]) BindUUIDs(ctx *gin.Context, extract func(T) []string) ([]uuid.UUID, bool) {
	var request T
	if err := ctx.ShouldBindJSON(&request); err != nil {
		ctx.JSON(http.StatusBadRequest, gin.H{
			"error":  "invalid request body",
			"detail": err.Error(),
		})
		return nil, false
	}

	rawIDs := extract(request)
	if len(rawIDs) == 0 {
		ctx.JSON(http.StatusBadRequest, gin.H{"error": "id list is required"})
		return nil, false
	}

	seen := make(map[uuid.UUID]bool, len(rawIDs))
	ids := make([]uuid.UUID, 0, len(rawIDs))
	for _, rawID := range rawIDs {
		id, err := uuid.Parse(rawID)
		if err != nil {
			ctx.JSON(http.StatusBadRequest, gin.H{
				"error":  fmt.Sprintf("invalid id %q", rawID),
				"detail": err.Error(),
			})
			return nil, false
		}
		if seen[id] {
			continue
		}
		seen[id] = true
		ids = append(ids, id)
	}

	if len(ids) == 0 {
		ctx.JSON(http.StatusBadRequest, gin.H{"error": "id list is required"})
		return nil, false
	}

	return ids, true
}

func (Helper[T]) MissingUUIDs(expected []uuid.UUID, existing map[uuid.UUID]bool) []string {
	missing := make([]string, 0)
	for _, id := range expected {
		if !existing[id] {
			missing = append(missing, id.String())
		}
	}
	return missing
}
