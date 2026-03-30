<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Achievement extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'is_prestige',
        'icon',
        'criteria_text',
    ];

    protected function casts(): array
    {
        return [
            'is_prestige' => 'boolean',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(UserProfile::class, 'user_achievements', 'achievement_id', 'user_profile_id')
            ->withPivot('unlocked_at')
            ->withTimestamps();
    }
}
