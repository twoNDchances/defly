package configs

import (
	"context"
	"defly-defender/ent"
	entdefender "defly-defender/ent/defender"
	"defly-defender/internal/globals"
	"fmt"

	_ "github.com/go-sql-driver/mysql"
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

	decisions, err := defender.QueryDecisions().All(ctx)
	if err != nil {
		return d.Error.LogString(fmt.Sprintf("failed to load decisions for defender %q: %v", d.Defender.Name, err))
	}

	globals.Pauser.Lock()
	defer globals.Pauser.Unlock()

	globals.Defender = defender
	globals.Principles = principles
	globals.Decisions = decisions

	return nil
}
