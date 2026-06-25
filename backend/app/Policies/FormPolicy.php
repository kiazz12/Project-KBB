<?php

namespace App\Policies;

use App\Models\Form;
use App\Models\User;

class FormPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role->value, ['super_admin', 'admin']);
    }

    public function view(User $user, Form $form): bool
    {
        return $user->role->value === 'super_admin' || $user->id === $form->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Form $form): bool
    {
        return $user->role->value === 'super_admin' || $user->id === $form->user_id;
    }

    public function delete(User $user, Form $form): bool
    {
        return $user->role->value === 'super_admin' || $user->id === $form->user_id;
    }
}
