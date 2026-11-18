<?php

namespace Database\Factories;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        $now = Carbon::now(Task::TIMEZONE);

        return [
            'pic_user_id' => User::factory()->state(['role' => 'pic', 'is_active' => true]),
            'intern_user_id' => User::factory()->state(['role' => 'intern', 'is_active' => true]),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(3),
            'assigned_at' => $now,
            'deadline_at' => $now->copy()->addDays(3),
            'status' => TaskStatus::Assigned,
            'accepted_at' => null,
            'accepted_by' => null,
        ];
    }

    public function accepted(?User $pic = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::Accepted,
            'accepted_at' => Carbon::now(Task::TIMEZONE),
            'accepted_by' => $pic?->id ?? $attributes['pic_user_id'],
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'deadline_at' => Carbon::now(Task::TIMEZONE)->subHours(5),
            'status' => TaskStatus::Assigned,
        ]);
    }
}
