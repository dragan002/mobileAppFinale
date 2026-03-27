<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'user_profile_id', 'name', 'color', 'is_preset', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_preset' => 'boolean',
        ];
    }

    public function habits(): HasMany
    {
        return $this->hasMany(Habit::class);
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
            'isPreset' => $this->is_preset,
            'sortOrder' => $this->sort_order,
        ];
    }
}
