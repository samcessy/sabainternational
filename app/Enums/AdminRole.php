<?php

namespace App\Enums;

/**
 * The four V1 admin roles. See docs/architecture/authorization-model.md §2-3
 * for why these four (not saba.md's original seven) and the full permission
 * matrix this enum implements.
 */
enum AdminRole: string
{
    case SuperAdministrator = 'super_administrator';
    case Editor = 'editor';
    case FinanceManager = 'finance_manager';
    case Viewer = 'viewer';

    /**
     * Get the display label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::SuperAdministrator => 'Super Administrator',
            self::Editor => 'Editor',
            self::FinanceManager => 'Finance Manager',
            self::Viewer => 'Viewer',
        };
    }

    /**
     * Get all the permissions for this role.
     *
     * @return array<AdminPermission>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::SuperAdministrator => AdminPermission::cases(),
            self::Editor => [
                AdminPermission::ViewContent,
                AdminPermission::ManageContent,
                AdminPermission::ViewImpactData,
                AdminPermission::ManageImpactData,
                AdminPermission::ViewEngagement,
                AdminPermission::ManageEngagement,
            ],
            self::FinanceManager => [
                AdminPermission::ViewContent,
                AdminPermission::ViewFundraising,
                AdminPermission::ManageFundraising,
                AdminPermission::ExportDonorData,
            ],
            self::Viewer => [
                AdminPermission::ViewContent,
                AdminPermission::ViewImpactData,
                AdminPermission::ViewEngagement,
                AdminPermission::ViewFundraising,
            ],
        };
    }

    /**
     * Determine if the role has the given permission.
     */
    public function hasPermission(AdminPermission $permission): bool
    {
        return in_array($permission, $this->permissions(), strict: true);
    }

    /**
     * Get all assignable roles for use in user-management UI.
     *
     * @return array<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->map(fn (self $role) => ['value' => $role->value, 'label' => $role->label()])
            ->values()
            ->all();
    }
}
