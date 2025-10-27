<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Form extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'title_ar',
        'description',
        'description_ar',
        'schema',
        'display',
        'status',
        'language',
        'is_public',
        'allow_anonymous',
        'settings',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'schema' => 'array',
            'display' => 'array',
            'settings' => 'array',
            'is_public' => 'boolean',
            'allow_anonymous' => 'boolean',
        ];
    }

    /**
     * Accessor for structure (alias for schema)
     * This allows analytics to access $form->structure
     */
    public function getStructureAttribute()
    {
        return $this->schema;
    }

    /**
     * Get the user that owns the form.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the submissions for the form.
     */
    public function submissions()
    {
        return $this->hasMany(FormSubmission::class);
    }

    /**
     * Scope a query to only include published forms.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to only include public forms.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Get the localized title.
     */
    public function getLocalizedTitle($locale = 'en')
    {
        return $locale === 'ar' && $this->title_ar ? $this->title_ar : $this->title;
    }

    /**
     * Get the localized description.
     */
    public function getLocalizedDescription($locale = 'en')
    {
        return $locale === 'ar' && $this->description_ar ? $this->description_ar : $this->description;
    }
}
