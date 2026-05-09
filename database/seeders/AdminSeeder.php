<?php

namespace Database\Seeders;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = env('ADMIN_EMAIL', 'admin@campuscrush.sn');
        $adminPassword = env('ADMIN_PASSWORD');

        if (!$adminPassword && app()->isLocal()) {
            $adminPassword = 'admin2026';
        }

        if (!$adminPassword) {
            $this->command->warn('ADMIN_PASSWORD manquant: compte admin non cree.');
            return;
        }

        $admin = User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Admin Campus Crush',
                'password' => Hash::make($adminPassword),
                'is_admin' => true,
            ]
        );

        Subscription::updateOrCreate(
            ['user_id' => $admin->id],
            [
                'status' => 'active',
                'amount' => 0,
                'starts_at' => now(),
                'ends_at' => now()->addYears(10),
            ]
        );

        if (env('BACKFILL_MISSING_TRIALS', false)) {
            $usersWithoutSub = User::doesntHave('subscription')->get();
            foreach ($usersWithoutSub as $user) {
                Subscription::createTrial($user->id);
            }

            $this->command->info('Essai gratuit cree pour ' . $usersWithoutSub->count() . ' utilisateurs');
        }

        $this->command->info('Admin cree : ' . $adminEmail);
    }
}
