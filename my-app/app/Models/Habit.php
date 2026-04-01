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
        'category_id', 'reminder_time', 'target_days_per_week',
    ];

    protected $attributes = [
        'target_days_per_week' => 7,
    ];

    protected function casts(): array
    {
        return [
            'target_days_per_week' => 'integer',
        ];
    }

    public function completions(): HasMany
    {
        return $this->hasMany(HabitCompletion::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Returns the current streak count as a plain integer.
     * Daily habits (target_days_per_week = 7) use day-based logic.
     * Frequency habits use week-based logic (weeks fully meeting target).
     */
    public function calculateStreak(): int
    {
        return $this->calculateStreakData()['value'];
    }

    /**
     * Returns structured streak data: { value, unit, graceDayActive }.
     * Used by toApiArray() and StateController.
     */
    public function calculateStreakData(): array
    {
        $completions = $this->completions()
            ->orderBy('completed_date', 'desc')
            ->pluck('completed_date')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        if ($this->target_days_per_week === 7) {
            return $this->calculateDailyStreakData($completions);
        }

        return $this->calculateWeekBasedStreakData($completions);
    }

    /**
     * @param  string[]  $completions  Dates descending (Y-m-d)
     * @return array{ value: int, unit: string, graceDayActive: bool }
     */
    private function calculateDailyStreakData(array $completions): array
    {
        $streak = 0;
        $expected = Carbon::today();
        $graceUsed = false;

        foreach ($completions as $date) {
            if ($date === $expected->format('Y-m-d')) {
                $streak++;
                $expected->subDay();
            } elseif (! $graceUsed && $date === $expected->copy()->subDay()->format('Y-m-d')) {
                $graceUsed = true;
                $streak++;
                $expected->subDays(2);
            } else {
                break;
            }
        }

        return [
            'value' => $streak,
            'unit' => 'days',
            'graceDayActive' => $graceUsed,
        ];
    }

    /**
     * @param  string[]  $completions  Dates descending (Y-m-d)
     * @return array{ value: int, unit: string, graceDayActive: bool }
     */
    private function calculateWeekBasedStreakData(array $completions): array
    {
        $streakWeeks = 0;
        $graceUsed = false;
        $currentWeekStart = Carbon::today()->startOfWeek();

        for ($weeks = 0; ; $weeks++) {
            $weekStart = $currentWeekStart->copy()->subWeeks($weeks);
            $weekEnd = $weekStart->copy()->addDays(6)->format('Y-m-d');
            $weekStartStr = $weekStart->format('Y-m-d');

            $weekCount = 0;
            foreach ($completions as $date) {
                if ($date >= $weekStartStr && $date <= $weekEnd) {
                    $weekCount++;
                }
            }

            if ($weekCount >= $this->target_days_per_week) {
                $streakWeeks++;
            } elseif ($weekCount >= ($this->target_days_per_week - 1) && ! $graceUsed) {
                $graceUsed = true;
                $streakWeeks++;
            } else {
                break;
            }
        }

        return [
            'value' => $streakWeeks,
            'unit' => 'weeks',
            'graceDayActive' => $graceUsed,
        ];
    }

    /**
     * Returns the best streak count as a plain integer.
     */
    public function calculateBestStreak(): int
    {
        return $this->calculateBestStreakData()['value'];
    }

    /**
     * Returns structured best streak data: { value, unit }.
     */
    public function calculateBestStreakData(): array
    {
        $completions = $this->completions()
            ->orderBy('completed_date')
            ->pluck('completed_date')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        if ($this->target_days_per_week === 7) {
            return [
                'value' => $this->calculateDailyBestStreak($completions),
                'unit' => 'days',
            ];
        }

        return [
            'value' => $this->calculateWeekBasedBestStreak($completions),
            'unit' => 'weeks',
        ];
    }

    /**
     * @param  string[]  $completions  Dates ascending (Y-m-d)
     */
    private function calculateDailyBestStreak(array $completions): int
    {
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
                $graceUsed = false;
                $current++;
            } elseif ($gap === 2 && ! $graceUsed) {
                $graceUsed = true;
                $current++;
            } else {
                $current = 1;
                $graceUsed = false;
            }

            if ($current > $best) {
                $best = $current;
            }
        }

        return $best;
    }

    /**
     * @param  string[]  $completions  Dates ascending (Y-m-d)
     */
    private function calculateWeekBasedBestStreak(array $completions): int
    {
        if (empty($completions)) {
            return 0;
        }

        // Build a map of week-start => completion count
        $weekCounts = [];
        foreach ($completions as $date) {
            $weekStart = Carbon::parse($date)->startOfWeek()->format('Y-m-d');
            $weekCounts[$weekStart] = ($weekCounts[$weekStart] ?? 0) + 1;
        }

        // Walk every calendar week from the first completion to today
        $firstDate = Carbon::parse(min(array_keys($weekCounts)))->startOfWeek();
        $todayWeek = Carbon::today()->startOfWeek();
        $target = $this->target_days_per_week;

        $best = 0;
        $current = 0;
        $graceUsed = false;
        $cursor = $firstDate->copy();

        while ($cursor->lte($todayWeek)) {
            $key = $cursor->format('Y-m-d');
            $count = $weekCounts[$key] ?? 0;

            if ($count >= $target) {
                $current++;
                $graceUsed = false;
            } elseif ($count >= ($target - 1) && ! $graceUsed) {
                $graceUsed = true;
                $current++;
            } else {
                $current = 0;
                $graceUsed = false;
            }

            if ($current > $best) {
                $best = $current;
            }

            $cursor->addWeek();
        }

        return $best;
    }

    public function calculatePhase(?int $streak = null): array
    {
        $streak = $streak ?? $this->calculateStreak();
        $daysSinceCreation = $this->created_at->diffInDays(Carbon::today());

        // Expected completions in 90 days, adjusted for frequency
        $expectedCompletions = $this->target_days_per_week * 13; // ~13 weeks in 90 days
        $recentCompletions = $this->completions()
            ->where('completed_date', '>=', Carbon::today()->subDays(90))
            ->count();
        $consistencyRate = min($recentCompletions / max($expectedCompletions, 1), 1.0);

        if ($daysSinceCreation <= 14) {
            return [
                'phase' => 'initiation',
                'label' => 'Getting Started',
                'description' => 'You\'re building the neural pathway. Every rep matters.',
                'daysSinceCreation' => $daysSinceCreation,
                'icon' => '🌱',
            ];
        } elseif ($daysSinceCreation <= 40) {
            return [
                'phase' => 'struggle',
                'label' => 'The Struggle',
                'description' => 'This is the valley of disappointment. Most people quit here. You haven\'t.',
                'daysSinceCreation' => $daysSinceCreation,
                'icon' => '⛰️',
            ];
        } elseif ($daysSinceCreation <= 66 || $consistencyRate < 0.75) {
            return [
                'phase' => 'autopilot',
                'label' => 'Autopilot Approaching',
                'description' => 'Your consistency rate is climbing. The habit is taking root.',
                'daysSinceCreation' => $daysSinceCreation,
                'consistencyRate' => round($consistencyRate * 100),
                'icon' => '🚀',
            ];
        } else {
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

    public function toApiArray(?array $precomputedStreakData = null, ?array $precomputedBestStreakData = null): array
    {
        $streakData = $precomputedStreakData ?? $this->calculateStreakData();
        $bestStreakData = $precomputedBestStreakData ?? $this->calculateBestStreakData();
        $phase = $this->calculatePhase($streakData['value'] ?? 0);

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
            'targetDaysPerWeek' => $this->target_days_per_week,
            'streakData' => $streakData,
            'bestStreakData' => $bestStreakData,
        ];
    }
}
