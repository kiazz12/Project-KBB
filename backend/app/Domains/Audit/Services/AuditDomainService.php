<?php

namespace App\Domains\Audit\Services;

use App\Models\AuditLog;
use App\Models\User;

class AuditDomainService
{
    /**
     * Log user action
     */
    public function logAction(
        User $user,
        string $action,
        ?string $auditableType = null,
        ?int $auditableId = null,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $user->id,
            'action' => $action,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Get user activity log
     */
    public function getUserActivityLog(User $user, int $limit = 50)
    {
        return AuditLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get action log for specific subject
     */
    public function getSubjectActivityLog(string $auditableType, int $auditableId, int $limit = 50)
    {
        return AuditLog::where('auditable_type', $auditableType)
            ->where('auditable_id', $auditableId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Log form action
     */
    public function logFormAction(User $user, string $action, int $formId, ?array $oldValues = null, ?array $newValues = null): AuditLog
    {
        return $this->logAction($user, $action, 'App\Models\Form', $formId, null, $oldValues, $newValues);
    }

    /**
     * Log submission action
     */
    public function logSubmissionAction(User $user, string $action, int $submissionId): AuditLog
    {
        return $this->logAction($user, $action, 'App\Models\FormSubmission', $submissionId);
    }

    /**
     * Log user management action
     */
    public function logUserAction(User $actor, string $action, int $targetUserId, ?array $oldValues = null, ?array $newValues = null): AuditLog
    {
        return $this->logAction($actor, $action, 'App\Models\User', $targetUserId, null, $oldValues, $newValues);
    }

    /**
     * Log authentication action
     */
    public function logAuthAction(User $user, string $action): AuditLog
    {
        return $this->logAction($user, $action, null, null, $action);
    }
}
