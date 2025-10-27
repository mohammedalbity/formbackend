<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'language',
        'timezone',
        'preferences',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'preferences' => 'array',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Get the forms created by this user.
     */
    public function forms()
    {
        return $this->hasMany(Form::class);
    }

    /**
     * Get the form submissions by this user.
     */
    public function submissions()
    {
        return $this->hasMany(FormSubmission::class);
    }

    /**
     * Get the form submissions reviewed by this user.
     */
    public function reviewedSubmissions()
    {
        return $this->hasMany(FormSubmission::class, 'reviewed_by');
    }

    /**
     * Get all submissions on forms created by this user.
     * This returns submissions from anyone on the user's forms.
     */
    public function formSubmissions()
    {
        return $this->hasManyThrough(
            FormSubmission::class,
            Form::class,
            'user_id',        // Foreign key on forms table
            'form_id',        // Foreign key on form_submissions table
            'id',             // Local key on users table
            'id'              // Local key on forms table
        );
    }

    /**
     * Set the role attribute with protection.
     * Prevents role escalation unless done through admin endpoints.
     */
    public function setRoleAttribute($value)
    {
        // If this is a new record (not yet saved), allow setting role
        if (!$this->exists) {
            $this->attributes['role'] = $value;
            return;
        }

        // If trying to change role on existing user, require admin authentication
        $currentUser = \Illuminate\Support\Facades\Auth::user();
        if ($currentUser && $currentUser->isAdmin()) {
            $this->attributes['role'] = $value;
        } else {
            // Log potential security issue
            \Log::warning('Unauthorized attempt to change user role', [
                'user_id' => $this->id,
                'attempted_by' => $currentUser?->id ?? 'guest',
                'attempted_role' => $value,
            ]);
            // Keep existing role
            // Don't throw exception to avoid breaking other updates
        }
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }
}
