<?php

namespace App\Policies;

use App\Models\FormSubmission;
use App\Models\User;

class FormSubmissionPolicy
{
    /**
     * Determine if the user can view any submissions.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view submissions list (filtered by ownership)
    }

    /**
     * Determine if the user can view the submission.
     */
    public function view(User $user, FormSubmission $submission): bool
    {
        // Admin can view all submissions
        if ($user->isAdmin()) {
            return true;
        }

        // Form owner can view submissions for their forms
        if ($submission->form->user_id === $user->id) {
            return true;
        }

        // Submission creator can view their own submission
        return $submission->user_id === $user->id;
    }

    /**
     * Determine if the user can create submissions.
     */
    public function create(User $user): bool
    {
        return true; // All users can create submissions
    }

    /**
     * Determine if the user can update the submission.
     */
    public function update(User $user, FormSubmission $submission): bool
    {
        // Admin can update all submissions
        if ($user->isAdmin()) {
            return true;
        }

        // Form owner can update submissions for their forms
        return $submission->form->user_id === $user->id;
    }

    /**
     * Determine if the user can delete the submission.
     */
    public function delete(User $user, FormSubmission $submission): bool
    {
        // Admin can delete all submissions
        if ($user->isAdmin()) {
            return true;
        }

        // Form owner can delete submissions for their forms
        return $submission->form->user_id === $user->id;
    }

    /**
     * Determine if the user can review the submission.
     */
    public function review(User $user, FormSubmission $submission): bool
    {
        // Admin can review all submissions
        if ($user->isAdmin()) {
            return true;
        }

        // Form owner can review submissions for their forms
        return $submission->form->user_id === $user->id;
    }

    /**
     * Determine if the user can approve the submission.
     */
    public function approve(User $user, FormSubmission $submission): bool
    {
        // Admin can approve all submissions
        if ($user->isAdmin()) {
            return true;
        }

        // Form owner can approve submissions for their forms
        return $submission->form->user_id === $user->id;
    }

    /**
     * Determine if the user can reject the submission.
     */
    public function reject(User $user, FormSubmission $submission): bool
    {
        // Admin can reject all submissions
        if ($user->isAdmin()) {
            return true;
        }

        // Form owner can reject submissions for their forms
        return $submission->form->user_id === $user->id;
    }
}
