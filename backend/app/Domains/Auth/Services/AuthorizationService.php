<?php

namespace App\Domains\Auth\Services;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\User;

class AuthorizationService
{
    public function canViewForm(User $user, Form $form): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $form->user_id === $user->id ||
               ($form->opd_id && $form->opd_id === $user->opd_id);
    }

    public function canEditForm(User $user, Form $form): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $form->user_id === $user->id;
    }

    public function canDeleteForm(User $user, Form $form): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $form->user_id === $user->id;
    }

    public function canViewSubmission(User $user, FormSubmission $submission): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        $form = $submission->form;

        return $form->user_id === $user->id ||
               ($form->opd_id && $form->opd_id === $user->opd_id);
    }

    public function canDeleteSubmission(User $user, FormSubmission $submission): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $submission->form->user_id === $user->id;
    }

    public function canExportForm(User $user, Form $form): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $form->user_id === $user->id ||
               ($form->opd_id && $form->opd_id === $user->opd_id);
    }

    public function canManageUsers(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function canManageOpdSettings(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function canViewAuditLogs(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function applyFormAccessConstraints($query, User $user)
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhere(function ($subQuery) use ($user) {
                    if ($user->opd_id) {
                        $subQuery->where('opd_id', $user->opd_id);
                    }
                });
        });
    }

    public function applySubmissionAccessConstraints($query, User $user)
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        return $query->whereHas('form', function ($formQuery) use ($user) {
            $formQuery->where('user_id', $user->id)
                ->orWhere(function ($q) use ($user) {
                    if ($user->opd_id) {
                        $q->where('opd_id', $user->opd_id);
                    }
                });
        });
    }
}
