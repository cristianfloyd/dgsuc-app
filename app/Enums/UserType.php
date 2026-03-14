<?php

namespace App\Enums;

enum UserType: string
{
    case Admin = 'admin';

    case ReportViewer = 'report_viewer';

    case WorkflowManager = 'workflow_manager';

    /**
     * @return array<int, string>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::Admin => ['access_admin_panel', 'view_reports', 'manage_users'],
            self::ReportViewer => ['view_reports'],
            self::WorkflowManager => ['manage_workflow', 'view_reports'],
        };
    }

    public function role(): string
    {
        return $this->value;
    }
}
