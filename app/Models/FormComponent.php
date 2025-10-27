<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FormComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'label',
        'label_ar',
        'key',
        'properties',
        'validation',
        'conditional',
        'description',
        'description_ar',
        'category',
        'sort_order',
        'is_active',
        'is_custom',
        'icon',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'validation' => 'array',
            'conditional' => 'array',
            'is_active' => 'boolean',
            'is_custom' => 'boolean',
        ];
    }

    /**
     * Scope a query to only include active components.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include components by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to order components by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Get the localized label.
     */
    public function getLocalizedLabel($locale = 'en')
    {
        return $locale === 'ar' && $this->label_ar ? $this->label_ar : $this->label;
    }

    /**
     * Get the localized description.
     */
    public function getLocalizedDescription($locale = 'en')
    {
        return $locale === 'ar' && $this->description_ar ? $this->description_ar : $this->description;
    }

    /**
     * Get components grouped by category.
     */
    public static function getByCategory($locale = 'en')
    {
        return static::active()
            ->ordered()
            ->get()
            ->groupBy('category')
            ->map(function ($components) use ($locale) {
                return $components->map(function ($component) use ($locale) {
                    return [
                        'id' => $component->id,
                        'type' => $component->type,
                        'label' => $component->getLocalizedLabel($locale),
                        'description' => $component->getLocalizedDescription($locale),
                        'key' => $component->key,
                        'properties' => $component->properties,
                        'validation' => $component->validation,
                        'conditional' => $component->conditional,
                        'icon' => $component->icon,
                    ];
                });
            });
    }
}
