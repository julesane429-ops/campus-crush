<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Profile;
use App\Models\Matche;
use App\Models\Message;
use App\Models\Subscription;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    private function findAvatar(string $prefix): ?string
    {
        foreach (['jpeg', 'jpg', 'png'] as $ext) {
            if (file_exists(public_path("images/avatars/{$prefix}.{$ext}"))) {
                return "avatars/{$prefix}.{$ext}";
            }
        }
        return null;
    }

    public function run(): void
    {
        // Mettre à jour les photos si les profils existent déjà
        if (User::where('email', 'test@ugb.edu.sn')->exists()) {
            $this->updatePhotos();
            return;
        }

        $password = Hash::make('password');
        $ufrs = ['SAT', 'SJP', 'S2ATA', 'LSH', 'SEFS'];
        $levels = ['L1', 'L2', 'L3', 'M1', 'M2'];

        $hommes = [
            'Moussa Diop', 'Ibrahima Fall', 'Ousmane Ndiaye', 'Abdoulaye Sow',
            'Mamadou Ba', 'Cheikh Sy', 'Modou Gueye', 'Pape Mbaye',
            'Aliou Diallo', 'Babacar Faye', 'Serigne Niang', 'Demba Cissé',
            'Lamine Sarr', 'Saliou Diouf', 'Assane Thiam', 'Elhadji Mbodj',
            'Malick Kane', 'Boubacar Touré', 'Daouda Wade', 'Thierno Ly',
        ];

        $femmes = [
            'Aminata Ndiaye', 'Fatou Diop', 'Awa Fall', 'Ndèye Sow',
            'Mariama Ba', 'Khady Sy', 'Coumba Gueye', 'Dieynaba Mbaye',
            'Astou Diallo', 'Rama Faye', 'Sokhna Niang', 'Adja Cissé',
            'Mame Sarr', 'Nabou Diouf', 'Yacine Thiam', 'Seynabou Mbodj',
            'Aïda Kane', 'Diary Touré', 'Binta Wade', 'Oumou Ly',
        ];

        $bios = [
            'Passionné(e) par la vie et les nouvelles rencontres 💫',
            'Étudiant(e) ambitieux(se), j\'aime le sport et la musique 🎵',
            'Fan de football et de séries Netflix 🍿',
            'La vie est belle quand on la partage ❤️',
            'Tech lover & entrepreneur en herbe 🚀',
            'J\'adore voyager et découvrir de nouvelles cultures 🌍',
            'Sourire, c\'est ma philosophie de vie 😊',
            'Créatif(ve) dans l\'âme, toujours en mouvement 🎨',
            'Le savoir est la clé du succès 📚',
            'Simple, humble et déterminé(e) 💪',
        ];

        $interests = [
            'Sport,Musique,Cinéma', 'Football,Jeux vidéo,Tech',
            'Lecture,Voyage,Cuisine', 'Danse,Mode,Entrepreneuriat',
            'Musique,Voyage,Sport', 'Tech,Lecture,Football',
            'Cuisine,Cinéma,Danse', 'Mode,Musique,Entrepreneuriat',
        ];

        foreach ($hommes as $i => $name) {
            $slug = strtolower(str_replace(' ', '.', $name));
            $user = User::create([
                'name' => $name,
                'email' => $slug . '@ugb.edu.sn',
                'password' => $password,
            ]);
            Profile::create([
                'user_id' => $user->id,
                'age' => rand(18, 25),
                'gender' => 'homme',
                'ufr' => $ufrs[array_rand($ufrs)],
                'level' => $levels[array_rand($levels)],
                'bio' => $bios[array_rand($bios)],
                'interests' => $interests[array_rand($interests)],
                'field_of_study' => 'Non précisé',
                'university' => 'UGB',
                'promotion' => 'P' . rand(28, 33),
                'photo' => $this->findAvatar('H' . ($i + 1)),
            ]);
            Subscription::createTrial($user->id);
        }

        foreach ($femmes as $i => $name) {
            $slug = strtolower(str_replace(' ', '.', $name));
            $user = User::create([
                'name' => $name,
                'email' => $slug . '@ugb.edu.sn',
                'password' => $password,
            ]);
            Profile::create([
                'user_id' => $user->id,
                'age' => rand(18, 24),
                'gender' => 'femme',
                'ufr' => $ufrs[array_rand($ufrs)],
                'level' => $levels[array_rand($levels)],
                'bio' => $bios[array_rand($bios)],
                'interests' => $interests[array_rand($interests)],
                'field_of_study' => 'Non précisé',
                'university' => 'UGB',
                'promotion' => 'P' . rand(28, 33),
                'photo' => $this->findAvatar('F' . ($i + 1)),
            ]);
            Subscription::createTrial($user->id);
        }

        $testH = User::create(['name' => 'Test Homme', 'email' => 'test@ugb.edu.sn', 'password' => $password]);
        Profile::create([
            'user_id' => $testH->id, 'age' => 21, 'gender' => 'homme',
            'ufr' => 'SAT', 'level' => 'L3', 'bio' => 'Compte test homme',
            'interests' => 'Sport,Tech', 'field_of_study' => 'Informatique',
            'university' => 'UGB', 'promotion' => 'P30',
        ]);
        Subscription::createTrial($testH->id);

        $testF = User::create(['name' => 'Aminata Test', 'email' => 'aminata@ugb.edu.sn', 'password' => $password]);
        Profile::create([
            'user_id' => $testF->id, 'age' => 20, 'gender' => 'femme',
            'ufr' => 'LSH', 'level' => 'L2', 'bio' => 'Compte test femme',
            'interests' => 'Musique,Danse', 'field_of_study' => 'Lettres',
            'university' => 'UGB', 'promotion' => 'P31',
        ]);
        Subscription::createTrial($testF->id);

        $match = Matche::create([
            'user1_id' => $testH->id,
            'user2_id' => $testF->id,
        ]);
        Message::create(['match_id' => $match->id, 'sender_id' => $testH->id, 'message' => 'Salut ! 👋']);
        Message::create(['match_id' => $match->id, 'sender_id' => $testF->id, 'message' => 'Hey ! Comment tu vas ? 😊']);

        echo "✅ 42 profils créés + 2 comptes test + 1 match démo\n";
    }

    private function updatePhotos(): void
    {
        $hommeUsers = User::whereHas('profile', fn($q) => $q->where('gender', 'homme'))
            ->where('email', '!=', 'admin@campuscrush.sn')
            ->where('email', '!=', 'test@ugb.edu.sn')
            ->orderBy('id')->get();
        foreach ($hommeUsers as $i => $u) {
            $photo = $this->findAvatar('H' . ($i + 1));
            if ($photo) $u->profile->update(['photo' => $photo]);
        }

        $femmeUsers = User::whereHas('profile', fn($q) => $q->where('gender', 'femme'))
            ->where('email', '!=', 'aminata@ugb.edu.sn')
            ->orderBy('id')->get();
        foreach ($femmeUsers as $i => $u) {
            $photo = $this->findAvatar('F' . ($i + 1));
            if ($photo) $u->profile->update(['photo' => $photo]);
        }

        echo "✅ Photos mises à jour\n";
    }
}