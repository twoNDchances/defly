package server

import (
	"context"
	"net/http"
	"strings"

	"defly-defender/ent"
	entuser "defly-defender/ent/user"
	"defly-defender/internal/configs"

	"github.com/gin-gonic/gin"
)

const (
	ActionApply     = "apply"
	ActionRevoke    = "revoke"
	ActionImplement = "implement"
	ActionSuspend   = "suspend"

	appliedForDefender = "Defender"
	defaultEmailHeader = "X-Executor"
	fullAction         = "all"
)

type Permission struct {
	Database configs.Database
	Email    string
}

func (p Permission) Apply() gin.HandlerFunc {
	return p.authorize(ActionApply)
}

func (p Permission) Revoke() gin.HandlerFunc {
	return p.authorize(ActionRevoke)
}

func (p Permission) Implement() gin.HandlerFunc {
	return p.authorize(ActionImplement)
}

func (p Permission) Suspend() gin.HandlerFunc {
	return p.authorize(ActionSuspend)
}

func (p Permission) authorize(action string) gin.HandlerFunc {
	return func(ctx *gin.Context) {
		email := strings.TrimSpace(ctx.GetHeader(p.emailHeaderKey()))
		if email == "" {
			ctx.AbortWithStatusJSON(http.StatusForbidden, gin.H{"error": "missing user email header"})
			return
		}

		allowed, err := p.can(ctx.Request.Context(), email, action)
		if err != nil {
			ctx.AbortWithStatusJSON(http.StatusInternalServerError, gin.H{"error": "failed to check permission"})
			return
		}
		if !allowed {
			ctx.AbortWithStatusJSON(http.StatusForbidden, gin.H{"error": "user does not have permission"})
			return
		}

		ctx.Next()
	}
}

func (p Permission) can(ctx context.Context, email string, action string) (bool, error) {
	client, err := p.Database.Connect()
	if err != nil {
		return false, err
	}
	defer client.Close()

	user, err := client.User.Query().
		Where(entuser.EmailEqualFold(email)).
		WithPermissions().
		WithGroups(func(groupQuery *ent.GroupQuery) {
			groupQuery.WithPermissions()
		}).
		Only(ctx)
	if ent.IsNotFound(err) {
		return false, nil
	}
	if err != nil {
		return false, err
	}

	if !user.IsVerified || !user.IsActivated {
		return false, nil
	}
	if user.IsRoot {
		return true, nil
	}
	if p.hasPermission(user.Edges.Permissions, action) {
		return true, nil
	}

	for _, group := range user.Edges.Groups {
		if p.hasPermission(group.Edges.Permissions, action) {
			return true, nil
		}
	}

	return false, nil
}

func (p Permission) hasPermission(permissions []*ent.Permission, action string) bool {
	for _, permission := range permissions {
		if permission == nil {
			continue
		}
		if !strings.EqualFold(permission.AppliedFor, appliedForDefender) {
			continue
		}
		if strings.EqualFold(permission.Action, fullAction) || strings.EqualFold(permission.Action, action) {
			return true
		}
	}

	return false
}

func (p Permission) emailHeaderKey() string {
	header := strings.TrimSpace(p.Email)
	if header == "" {
		return defaultEmailHeader
	}

	return header
}
