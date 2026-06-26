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
        string $subject,
        ?int $subjectId = null,
        array $changes = []
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $user->id,
            'action' => $action,
            'subject' => $subject,
            'subject_id' => $subjectId,
            'changes' => $changes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now(),
        ]);
    }

    /**
     * Get user activity log
     */
    public function getUserActivityLog(User $user, int $limit = 50)
    {
        return AuditLog::where('user_id', $user->id)
            ->orderBy('timestamp', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get action log for specific subject
     */
    public function getSubjectActivityLog(string $subject, int $subjectId, int $limit = 50)
    {
        return AuditLog::where('subject', $subject)
            ->where('subject_id', $subjectId)
            ->orderBy('timestamp', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get audit log for specific time range
     */
    public function getAuditLogByDateRange(\DateTime $from, \DateTime $to, int $limit = 100)
    {
        return AuditLog::whereBetween('timestamp', [$from, $to])
            ->orderBy('timestamp', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Log form action
     */
    public function logFormAction(User $user, string $action, int $formId, array $changes = []): AuditLog
    {
        return $this->logAction($user, $action, 'Form', $formId, $changes);
    }

    /**
     * Log submission action
     */
    public function logSubmissionAction(User $user, string $action, int $submissionId, array $changes = []): AuditLog
    {
        return $this->logAction($user, $action, 'FormSubmission', $submissionId, $changes);
    }

    /**
     * Log user management action
     */
    public function logUserAction(User $actor, string $action, int $targetUserId, array $changes = []): AuditLog
    {
        return $this->logAction($actor, $action, 'User', $targetUserId, $changes);
    }

    /**
     * Log authentication action
     */
    public function logAuthAction(User $user, string $action): AuditLog
    {
        return $this->logAction($user, $action, 'Authentication');
    }
}
