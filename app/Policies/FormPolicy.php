<?php

namespace App\Policies;

use App\Models\Form;
use App\Models\User;

class FormPolicy
{
    /**
     * Determine if the user can view any forms.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view forms list
    }

    /**
     * Determine if the user can view the form.
     */
    public function view(User $user, Form $form): bool
    {
        // Admin can view all forms
        if ($user->isAdmin()) {
            return true;
        }

        // Form owner can view their forms
        if ($form->user_id === $user->id) {
            return true;
        }

        // Public forms can be viewed by anyone
        return $form->is_public;
    }

    /**
     * Determine if the user can create forms.
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create forms
    }

    /**
     * Determine if the user can update the form.
     */
    public function update(User $user, Form $form): bool
    {
        // Admin can update all forms
        if ($user->isAdmin()) {
            return true;
        }

        // Form owner can update their forms
        return $form->user_id === $user->id;
    }

    /**
     * Determine if the user can delete the form.
     */
    public function delete(User $user, Form $form): bool
    {
        // Admin can delete all forms
        if ($user->isAdmin()) {
            return true;
        }

        // Form owner can delete their forms
        return $form->user_id === $user->id;
    }

    /**
     * Determine if the user can restore the form.
     */
    public function restore(User $user, Form $form): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can permanently delete the form.
     */
    public function forceDelete(User $user, Form $form): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can view form submissions.
     */
    public function viewSubmissions(User $user, Form $form): bool
    {
        // Admin can view all submissions
        if ($user->isAdmin()) {
            return true;
        }

        // Form owner can view their form's submissions
        return $form->user_id === $user->id;
    }
}
