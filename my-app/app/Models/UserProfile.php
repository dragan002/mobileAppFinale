<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UserProfile extends Model
{
    protected $table = 'user_profile';

    protected $fillable = [
        'name',
        'identity',
        'identity_label',
        'identity_icon',
    ];

    public function achievements(): BelongsToMany
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements', 'user_profile_id', 'achievement_id')
            ->withPivot('unlocked_at')
            ->withTimestamps();
    }

    public function hasAchievement(string $code): bool
    {
        $achievement = Achievement::where('code', $code)->first();

        if (! $achievement) {
            return false;
        }

        return UserAchievement::where('user_profile_id', $this->id)
            ->where('achievement_id', $achievement->id)
            ->exists();
    }

    /**
     * Unlock an achievement by code. Idempotent — safe to call multiple times.
     * Returns the Achievement if newly unlocked, or null if already unlocked.
     */
    public function unlockAchievement(string $code): ?Achievement
    {
        $achievement = Achievement::where('code', $code)->first();

        if (! $achievement) {
            return null;
        }

        $alreadyUnlocked = UserAchievement::where('user_profile_id', $this->id)
            ->where('achievement_id', $achievement->id)
            ->exists();

        if ($alreadyUnlocked) {
            return null;
        }

        UserAchievement::create([
            'user_profile_id' => $this->id,
            'achievement_id' => $achievement->id,
            'unlocked_at' => now(),
        ]);

        return $achievement;
    }
}
