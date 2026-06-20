# Label

A Label is colored metadata for classifying and filtering Manager resources. It does not participate in firewall logic, change permissions, or load into Defender for HTTP processing.

## Configuration Fields

| Field | Required | Constraint |
| --- | --- | --- |
| `name` | Yes | Unique lowercase kebab-case, up to 255 characters. |
| `color` | Yes | Valid hex color. |
| `description` | No | Explains the Label convention. |

A Label has a UUID, `created_by`, and timestamps.

## Supported Resources

Labels use the polymorphic `labels_resources` relationship and currently support:

- [User](User.md)
- [Group](Group.md)
- [Permission](Permission.md)
- [Wordlist](Wordlist.md)
- [Engine](Engine.md)
- [Target](Target.md)
- [Action](Action.md)
- [Rule](Rule.md)
- [Principle](Principle.md)
- [Decision](Decision.md)
- [Defender](Defender.md)

Pattern, Key, Report, and Timeline have no Label relationship in the current model.

## Usage

A resource may have multiple Labels, and one Label may attach to multiple resource types. Useful conventions include:

- Environment: `production`, `staging`, `development`.
- Administration level: `critical`, `experimental`, `legacy`.
- Policy family: `authentication`, `upload`, `api-abuse`.
- Additional workflow state: `needs-review`, `approved`.

Do not imitate fields such as `validation_status`, `deployment_status`, `is_applied`, or `is_implemented`; workflows do not update Labels automatically.

## Label Does Not Replace Permission

Attaching `production` to a Defender does not restrict deployment. Attaching `approved` to a Principle does not replace `passed` validation. [Permission](Permission.md), policy classes, lock state, and record state still control access.

## Audit History

Creating, changing, and deleting Labels is recorded in [Timeline](Timeline.md). Label relationships support search/presentation and do not enter firewall reports.

## Checklist

- Define consistent Label names before broad use.
- Use color as a visual aid, not the sole source of truth.
- Do not replace Permission or workflow state with Labels.
- Delete carefully because one Label may classify many resource types.
