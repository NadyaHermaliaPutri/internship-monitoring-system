<?php

namespace Database\Factories;

use App\Enums\LogbookSource;
use App\Enums\LogbookStatus;
use App\Models\Logbook;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Logbook>
 */
class LogbookFactory extends Factory
{
    protected $model = Logbook::class;

    public function definition(): array
    {
        return [
            'intern_user_id' => User::factory()->state(['role' => 'intern', 'is_active' => true]),
            'log_date' => Carbon::now(Logbook::TIMEZONE)->toDateString(),
            'title' => fake()->sentence(4),
            'activity_description' => fake()->paragraph(3),
            'result' => fake()->sentence(8),
            'source' => LogbookSource::Manual,
            'task_id' => null,
            'status' => LogbookStatus::Pending,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'review_note' => null,
        ];
    }

    public function approved(?User $pic = null): static
    {
        return $this->state(fn () => [
            'status' => LogbookStatus::Approved,
            'reviewed_by' => $pic?->id ?? User::factory()->state(['role' => 'pic', 'is_active' => true]),
            'reviewed_at' => Carbon::now(Logbook::TIMEZONE),
            'review_note' => 'Kegiatan telah sesuai dan diverifikasi.',
        ]);
    }

    public function revisionRequested(?User $pic = null, string $note = 'Mohon lengkapi deskripsi hasil kegiatan.'): static
    {
        return $this->state(fn () => [
            'status' => LogbookStatus::RevisionRequested,
            'reviewed_by' => $pic?->id ?? User::factory()->state(['role' => 'pic', 'is_active' => true]),
            'reviewed_at' => Carbon::now(Logbook::TIMEZONE),
            'review_note' => $note,
        ]);
    }

    public function fromTaskAssignment(int $taskId, ?string $title = null): static
    {
        return $this->state(fn () => [
            'title' => $title ?? 'Penugasan: '.fake()->sentence(3),
            'activity_description' => 'Catatan otomatis sistem saat PIC memberikan tugas kepada peserta.',
            'result' => null,
            'source' => LogbookSource::TaskAssignment,
            'task_id' => $taskId,
            'status' => null,
        ]);
    }
}
