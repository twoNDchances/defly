package configs

import (
	"context"
	"database/sql"
	"defly-defender/ent"
	entdefender "defly-defender/ent/defender"
	"defly-defender/internal/globals"
	"fmt"
	"strings"

	_ "github.com/go-sql-driver/mysql"
	"github.com/google/uuid"
)

type Database struct {
	Defender Defender
	Address  Address
	Name     string
	User     string
	Pass     string
	Error    Error
}

func (d Database) DSN() string {
	return fmt.Sprintf(
		"%s:%s@tcp(%s:%s)/%s?charset=utf8mb4&parseTime=True&loc=Local",
		d.User,
		d.Pass,
		d.Address.Host,
		d.Address.Port,
		d.Name,
	)
}

func (d Database) Connect() (*ent.Client, error) {
	return ent.Open("mysql", d.DSN())
}

func (d Database) PrincipleIDsByPivotOrder(ctx context.Context, defenderID uuid.UUID, principleIDs []uuid.UUID) ([]uuid.UUID, error) {
	return d.resourceIDsByPivotOrder(ctx, "defenders_principles", "principle", defenderID, principleIDs)
}

func (d Database) DecisionIDsByPivotOrder(ctx context.Context, defenderID uuid.UUID, decisionIDs []uuid.UUID) ([]uuid.UUID, error) {
	return d.resourceIDsByPivotOrder(ctx, "defenders_decisions", "decision", defenderID, decisionIDs)
}

func (d Database) resourceIDsByPivotOrder(ctx context.Context, table string, resourceColumn string, defenderID uuid.UUID, resourceIDs []uuid.UUID) ([]uuid.UUID, error) {
	db, err := sql.Open("mysql", d.DSN())
	if err != nil {
		return nil, err
	}
	defer db.Close()

	args := make([]any, 0, len(resourceIDs)+1)
	args = append(args, defenderID.String())

	query := fmt.Sprintf(
		"SELECT %s FROM %s WHERE defender = ?",
		resourceColumn,
		table,
	)
	if len(resourceIDs) > 0 {
		placeholders := strings.TrimRight(strings.Repeat("?,", len(resourceIDs)), ",")
		query += fmt.Sprintf(" AND %s IN (%s)", resourceColumn, placeholders)
		for _, id := range resourceIDs {
			args = append(args, id.String())
		}
	}
	query += " ORDER BY `order` ASC"

	rows, err := db.QueryContext(ctx, query, args...)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	idsByPivotOrder := make([]uuid.UUID, 0, len(resourceIDs))
	for rows.Next() {
		var rawID string
		if err := rows.Scan(&rawID); err != nil {
			return nil, err
		}

		id, err := uuid.Parse(rawID)
		if err != nil {
			return nil, err
		}
		idsByPivotOrder = append(idsByPivotOrder, id)
	}
	if err := rows.Err(); err != nil {
		return nil, err
	}

	return idsByPivotOrder, nil
}

func (d Database) LoadGlobals(ctx context.Context) error {
	errorFile, err := d.Error.Boot()
	if err != nil {
		return d.Error.LogError(err)
	}
	if errorFile != nil {
		defer errorFile.Close()
	}

	client, err := d.Connect()
	if err != nil {
		return d.Error.LogError(err)
	}
	defer client.Close()

	defender, err := client.Defender.Query().
		Where(entdefender.NameEQ(d.Defender.Name)).
		Only(ctx)
	if err != nil {
		return d.Error.LogString(fmt.Sprintf("failed to load defender %q: %v", d.Defender.Name, err))
	}

	principles, err := defender.QueryPrinciples().
		WithRules(func(ruleQuery *ent.RuleQuery) {
			ruleQuery.WithTarget(func(targetQuery *ent.TargetQuery) {
				targetQuery.WithPattern()
				targetQuery.WithWordlist()
				targetQuery.WithEngines()
			})
			ruleQuery.WithWordlist()
			ruleQuery.WithActions()
		}).
		All(ctx)
	if err != nil {
		return d.Error.LogString(fmt.Sprintf("failed to load principles for defender %q: %v", d.Defender.Name, err))
	}
	principleIDsByPivotOrder, err := d.PrincipleIDsByPivotOrder(ctx, defender.ID, nil)
	if err != nil {
		return d.Error.LogString(fmt.Sprintf("failed to order principles for defender %q: %v", d.Defender.Name, err))
	}
	principles = d.SortPrinciplesByPivotOrder(principles, principleIDsByPivotOrder)

	decisions, err := defender.QueryDecisions().All(ctx)
	if err != nil {
		return d.Error.LogString(fmt.Sprintf("failed to load decisions for defender %q: %v", d.Defender.Name, err))
	}
	decisionIDsByPivotOrder, err := d.DecisionIDsByPivotOrder(ctx, defender.ID, nil)
	if err != nil {
		return d.Error.LogString(fmt.Sprintf("failed to order decisions for defender %q: %v", d.Defender.Name, err))
	}
	decisions = d.SortDecisionsByPivotOrder(decisions, decisionIDsByPivotOrder)

	globals.Pauser.Lock()
	defer globals.Pauser.Unlock()

	globals.Defender = defender
	globals.Principles = principles
	globals.Decisions = decisions

	return nil
}

func (d Database) SortPrinciplesByPivotOrder(principles []*ent.Principle, principleIDsByPivotOrder []uuid.UUID) []*ent.Principle {
	byID := make(map[uuid.UUID]*ent.Principle, len(principles))
	for _, principle := range principles {
		if principle != nil {
			byID[principle.ID] = principle
		}
	}

	ordered := make([]*ent.Principle, 0, len(principles))
	for _, id := range principleIDsByPivotOrder {
		if principle, ok := byID[id]; ok {
			ordered = append(ordered, principle)
			delete(byID, id)
		}
	}
	for _, principle := range principles {
		if principle != nil && byID[principle.ID] != nil {
			ordered = append(ordered, principle)
			delete(byID, principle.ID)
		}
	}

	return ordered
}

func (d Database) SortDecisionsByPivotOrder(decisions []*ent.Decision, decisionIDsByPivotOrder []uuid.UUID) []*ent.Decision {
	byID := make(map[uuid.UUID]*ent.Decision, len(decisions))
	for _, decision := range decisions {
		if decision != nil {
			byID[decision.ID] = decision
		}
	}

	ordered := make([]*ent.Decision, 0, len(decisions))
	for _, id := range decisionIDsByPivotOrder {
		if decision, ok := byID[id]; ok {
			ordered = append(ordered, decision)
			delete(byID, id)
		}
	}
	for _, decision := range decisions {
		if decision != nil && byID[decision.ID] != nil {
			ordered = append(ordered, decision)
			delete(byID, decision.ID)
		}
	}

	return ordered
}
