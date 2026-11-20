<?php

namespace Database\Seeders;

use App\Models\EwsSetting;
use App\Models\User;
use Illuminate\Database\Seeder;

class EwsSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        EwsSetting::firstOrCreate(
            ['id' => 1],
            [
                'attendance_cutoff_time' => '09:00',
                'logbook_cutoff_time' => '17:00',
                'updated_by' => $admin?->id,
            ]
        );
    }
}
