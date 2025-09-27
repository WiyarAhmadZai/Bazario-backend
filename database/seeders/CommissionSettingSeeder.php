<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CommissionSetting;
use App\Models\User;

class CommissionSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first admin user
        $admin = User::role('admin')->first();

        // Create default commission setting
        CommissionSetting::create([
            'percentage' => 2.00, // 2% commission
            'updated_by' => $admin ? $admin->id : null,
        ]);
    }
}
