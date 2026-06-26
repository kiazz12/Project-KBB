<?php

namespace App\Domains\Auth\Services;

use App\Models\User;
use App\Models\Form;
use App\Models\FormSubmission;

class AuthorizationService
{
    /**
     * Check if user can view form
     */
    public function canViewForm(User $user, Form $form): bool
    {
        // Super admin can view all forms
        if ($user->isSuperAdmin()) {
            return true;
        }

        // User can view own form or form in same OPD
        return $form->user_id === $user->id || 
               ($form->opd_id && $form->opd_id === $user->opd_id);
    }

    /**
     * Check if user can edit form
     */
    public function canEditForm(User $user, Form $form): bool
    {
        // Super admin can edit all forms
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Only creator or super admin can edit
        return $form->user_id === $user->id;
    }

    /**
     * Check if user can delete form
     */
    public function canDeleteForm(User $user, Form $form): bool
    {
        // Super admin can delete all forms
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Only creator can delete
        return $form->user_id === $user->id;
    }

    /**
     * Check if user can view submission
     */
    public function canViewSubmission(User $user, FormSubmission $submission): bool
    {
        // Super admin can view all
        if ($user->isSuperAdmin()) {
            return true;
        }

        // User can view submission only if:
        // - They own the form, OR
        // - Form belongs to their OPD and they're in same OPD
        $form = $submission->form;
        return $form->user_id === $user->id || 
               ($form->opd_id && $form->opd_id === $user->opd_id);
    }

    /**
     * Check if user can delete submission
     */
    public function canDeleteSubmission(User $user, FormSubmission $submission): bool
    {
        // Super admin can delete all
        if ($user->isSuperAdmin()) {
            return true;
        }

        // User can delete if they own the form
        return $submission->form->user_id === $user->id;
    }

    /**
     * Check if user can export form data
     */
    public function canExportForm(User $user, Form $form): bool
    {
        // Super admin can export all
        if ($user->isSuperAdmin()) {
            return true;
        }

        // User can export only their own forms or forms in same OPD
        return $form->user_id === $user->id || 
               ($form->opd_id && $form->opd_id === $user->opd_id);
    }

    /**
     * Check if user can manage users
     */
    public function canManageUsers(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Check if user can manage OPD settings
     */
    public function canManageOpdSettings(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Check if user can view audit logs
     */
    public function canViewAuditLogs(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Get query constraints for forms user can access
     */
    public function applyFormAccessConstraints($query, User $user)
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        // User can see own forms or forms in same OPD
        return $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere(function ($subQuery) use ($user) {
                  if ($user->opd_id) {
                      $subQuery->where('opd_id', $user->opd_id);
                  }
              });
        });
    }

    /**
     * Get query constraints for submissions user can access
     */
    public function applySubmissionAccessConstraints($query, User $user)
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        // User can see submissions from forms they own or forms in their OPD
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
