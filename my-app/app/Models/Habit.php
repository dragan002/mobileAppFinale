<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Habit extends Model
{
    protected $fillable = [
        'name', 'emoji', 'color', 'time_of_day',
        'why', 'bundle', 'two_min_version', 'stack',
        'duration', 'reward', 'difficulty',
        'category_id', 'reminder_time',
    ];

    public function completions(): HasMany
    {
        return $this->hasMany(HabitCompletion::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function calculateStreak(): int
    {
        $completions = $this->completions()
            ->orderBy('completed_date', 'desc')
            ->pluck('completed_date')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        $streak = 0;
        $expected = Carbon::today();
        $graceUsed = false;

        foreach ($completions as $date) {
            if ($date === $expected->format('Y-m-d')) {
                $streak++;
                $expected->subDay();
            } elseif (! $graceUsed && $date === $expected->subDay()->format('Y-m-d')) {
                // One missed day — apply the grace day and count this completion
                $graceUsed = true;
                $streak++;
                $expected->subDay();
            } else {
                break;
            }
        }

        return $streak;
    }

    public function calculateBestStreak(): int
    {
        $completions = $this->completions()
            ->orderBy('completed_date')
            ->pluck('completed_date')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        if (empty($completions)) {
            return 0;
        }

        $best = 1;
        $current = 1;
        $graceUsed = false;

        for ($i = 1; $i < count($completions); $i++) {
            $prev = Carbon::parse($completions[$i - 1]);
            $curr = Carbon::parse($completions[$i]);
            $gap = (int) $prev->diffInDays($curr);

            if ($gap === 1) {
                // Consecutive day — continue streak, reset grace availability
                $graceUsed = false;
                $current++;
            } elseif ($gap === 2 && ! $graceUsed) {
                // Exactly one missed day — apply grace and continue
                $graceUsed = true;
                $current++;
            } else {
                // Two or more consecutive missing days — reset
                $current = 1;
                $graceUsed = false;
            }

            if ($current > $best) {
                $best = $current;
            }
        }

        return $best;
    }

    public function calculatePhase(): array
    {
        $streak = $this->calculateStreak();
        $daysSinceCreation = $this->created_at->diffInDays(Carbon::today());

        // Calculate consistency rate (completions in last 90 days / 90)
        $recentCompletions = $this->completions()
            ->where('completed_date', '>=', Carbon::today()->subDays(90))
            ->count();
        $consistencyRate = min($recentCompletions / 90, 1.0);

        // Determine phase based on days and consistency
        if ($daysSinceCreation <= 14) {
            return [
                'phase' => 'initiation',
                'label' => 'Getting Started',
                'description' => 'You\'re building the neural pathway. Every rep matters.',
                'daysSinceCreation' => $daysSinceCreation,
                'icon' => '🌱',
            ];
        } elseif ($daysSinceCreation <= 40) {
            // Struggle phase - the valley of disappointment
            return [
                'phase' => 'struggle',
                'label' => 'The Struggle',
                'description' => 'This is the valley of disappointment. Most people quit here. You haven\'t.',
                'daysSinceCreation' => $daysSinceCreation,
                'icon' => '⛰️',
            ];
        } elseif ($daysSinceCreation <= 66 || $consistencyRate < 0.75) {
            // Autopilot approach - consistency is building
            return [
                'phase' => 'autopilot',
                'label' => 'Autopilot Approaching',
                'description' => 'Your consistency rate is climbing. The habit is taking root.',
                'daysSinceCreation' => $daysSinceCreation,
                'consistencyRate' => round($consistencyRate * 100),
                'icon' => '🚀',
            ];
        } else {
            // Identity phase - it's who you are
            return [
                'phase' => 'identity',
                'label' => 'Identity',
                'description' => 'This isn\'t something you do. It\'s who you are.',
                'daysSinceCreation' => $daysSinceCreation,
                'consistencyRate' => round($consistencyRate * 100),
                'icon' => '⭐',
            ];
        }
    }

    public function toApiArray(): array
    {
        $phase = $this->calculatePhase();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'emoji' => $this->emoji,
            'color' => $this->color,
            'time' => $this->time_of_day,
            'why' => $this->why ?? '',
            'bundle' => $this->bundle ?? '',
            'twoMin' => $this->two_min_version ?? '',
            'stack' => $this->stack ?? '',
            'duration' => $this->duration ?? '',
            'reward' => $this->reward ?? '',
            'diff' => $this->difficulty,
            'createdAt' => $this->created_at->format('Y-m-d'),
            'categoryId' => $this->category_id,
            'reminderTime' => $this->reminder_time ?? '',
            'phase' => $phase,
        ];
    }
}
