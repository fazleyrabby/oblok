<?php

namespace App\Enums;

enum ProjectRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Operator = 'operator';
    case Viewer = 'viewer';

    /**
     * Human-readable label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Admin => 'Admin',
            self::Operator => 'Operator',
            self::Viewer => 'Viewer',
        };
    }

    /**
     * Ability grants for this role.
     *
     * @return array<int, string>
     */
    public function abilities(): array
    {
        return match ($this) {
            self::Owner => [
                'view',
                'update',
                'delete',
                'restore',
                'forceDelete',
                'manageMembers',
                'manageServices',
                'manageIncidents',
                'manageAlerts',
                'ingestLogs',
                'manageDeployments',
                'manageWebhooks',
                'manageScheduler',
            ],
            self::Admin => [
                'view',
                'update',
                'manageMembers',
                'manageServices',
                'manageIncidents',
                'manageAlerts',
                'ingestLogs',
                'manageWebhooks',
                'manageScheduler',
            ],
            self::Operator => [
                'view',
                'manageServices',
                'manageIncidents',
                'ingestLogs',
                'manageWebhooks',
                'manageScheduler',
            ],
            self::Viewer => [
                'view',
            ],
        };
    }

    /**
     * Determine whether this role grants the given ability.
     */
    public function can(string $ability): bool
    {
        return in_array($ability, $this->abilities(), true);
    }

    /**
     * The default role assigned to a new member.
     */
    public static function default(): self
    {
        return self::Operator;
    }
}
