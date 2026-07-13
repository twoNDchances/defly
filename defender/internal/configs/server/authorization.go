package server

import (
	"context"
	"net/http"
	"strings"
	"time"

	"defly-defender/ent"
	entdefender "defly-defender/ent/defender"
	entguard "defly-defender/ent/guard"
	entuser "defly-defender/ent/user"
	"defly-defender/internal/configs"

	"github.com/gin-gonic/gin"
)

const (
	ActionApply     = "apply"
	ActionRevoke    = "revoke"
	ActionImplement = "implement"
	ActionSuspend   = "suspend"

	appliedForPrinciple = "Principle"
	appliedForDecision  = "Decision"
	defaultEmailHeader  = "X-Executor"
	fullAction          = "all"
)

type Authorization struct {
	Database configs.Database
	Email    string
	Error    configs.Error
}

func (a Authorization) Apply() gin.HandlerFunc {
	return a.authorize(ActionApply, appliedForPrinciple)
}

func (a Authorization) Revoke() gin.HandlerFunc {
	return a.authorize(ActionRevoke, appliedForPrinciple)
}

func (a Authorization) Implement() gin.HandlerFunc {
	return a.authorize(ActionImplement, appliedForDecision)
}

func (a Authorization) Suspend() gin.HandlerFunc {
	return a.authorize(ActionSuspend, appliedForDecision)
}

func (a Authorization) authorize(action, appliedFor string) gin.HandlerFunc {
	return func(ctx *gin.Context) {
		email := strings.TrimSpace(ctx.GetHeader(a.emailHeaderKey()))
		if email == "" {
			ctx.AbortWithStatusJSON(http.StatusForbidden, gin.H{"error": "missing user email header"})
			return
		}

		allowed, err := a.can(ctx.Request.Context(), email, action, appliedFor)
		if err != nil {
			_ = a.Error.LogError(err)
			ctx.AbortWithStatusJSON(http.StatusInternalServerError, gin.H{"error": "internal server error"})
			return
		}
		if !allowed {
			ctx.AbortWithStatusJSON(http.StatusForbidden, gin.H{"error": "user does not have permission"})
			return
		}

		ctx.Next()
	}
}

func (a Authorization) can(ctx context.Context, email, action, appliedFor string) (bool, error) {
	client, err := a.Database.Connect()
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
		return a.guardAllows(ctx, client, user)
	}
	if a.hasPermission(user.Edges.Permissions, action, appliedFor) {
		return a.guardAllows(ctx, client, user)
	}

	for _, group := range user.Edges.Groups {
		if a.hasPermission(group.Edges.Permissions, action, appliedFor) {
			return a.guardAllows(ctx, client, user)
		}
	}

	return false, nil
}

func (a Authorization) guardAllows(ctx context.Context, client *ent.Client, user *ent.User) (bool, error) {
	defender, err := client.Defender.Query().
		Where(entdefender.NameEQ(a.Database.Defender.Name)).
		Only(ctx)
	if ent.IsNotFound(err) {
		return true, nil
	}
	if err != nil {
		return false, err
	}

	if defender.CreatedBy != nil && *defender.CreatedBy == user.ID {
		return true, nil
	}

	guardedDefender, err := client.Guard.Query().
		Where(entguard.HasDefendersWith(entdefender.IDEQ(defender.ID))).
		Exist(ctx)
	if err != nil {
		return false, err
	}
	if !guardedDefender {
		return true, nil
	}

	return client.Guard.Query().
		Where(
			entguard.HasDefendersWith(entdefender.IDEQ(defender.ID)),
			entguard.HasUsersWith(entuser.IDEQ(user.ID)),
			entguard.Or(
				entguard.ExpiredAtIsNil(),
				entguard.ExpiredAtGT(time.Now()),
			),
		).
		Exist(ctx)
}

func (a Authorization) hasPermission(permissions []*ent.Permission, action, appliedFor string) bool {
	for _, permission := range permissions {
		if permission == nil {
			continue
		}
		if !strings.EqualFold(permission.AppliedFor, appliedFor) {
			continue
		}
		if strings.EqualFold(permission.Action, fullAction) || strings.EqualFold(permission.Action, action) {
			return true
		}
	}

	return false
}

func (a Authorization) emailHeaderKey() string {
	header := strings.TrimSpace(a.Email)
	if header == "" {
		return defaultEmailHeader
	}

	return header
}
