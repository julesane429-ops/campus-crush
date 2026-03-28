<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Créer ou mettre à jour le compte admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@campuscrush.sn'],
            [
                'name' => 'Admin Campus Crush',
                'password' => Hash::make('admin2026'),
                'is_admin' => true,
            ]
        );

        // Donner un abonnement permanent à l'admin
        Subscription::updateOrCreate(
            ['user_id' => $admin->id],
            [
                'status' => 'active',
                'amount' => 0,
                'starts_at' => now(),
                'ends_at' => now()->addYears(10),
            ]
        );

        // Créer un essai gratuit pour tous les utilisateurs qui n'ont pas d'abonnement
        $usersWithoutSub = User::doesntHave('subscription')->get();
        foreach ($usersWithoutSub as $user) {
            Subscription::createTrial($user->id);
        }

        $this->command->info('✅ Admin créé : admin@campuscrush.sn / admin2026');
        $this->command->info('✅ Essai gratuit créé pour ' . $usersWithoutSub->count() . ' utilisateurs');
    }
}
