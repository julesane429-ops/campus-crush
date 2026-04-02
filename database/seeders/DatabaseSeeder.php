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
        // Si la BDD est déjà seedée, on ajoute seulement les nouveaux profils féminins
        if (User::where('email', 'test@ugb.edu.sn')->exists()) {
            $this->addNewFemaleProfiles();
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

        // 20 profils féminins originaux (F1–F20)
        $femmes = [
            'Aminata Ndiaye', 'Fatou Diop', 'Awa Fall', 'Ndèye Sow',
            'Mariama Ba', 'Khady Sy', 'Coumba Gueye', 'Dieynaba Mbaye',
            'Astou Diallo', 'Rama Faye', 'Sokhna Niang', 'Adja Cissé',
            'Mame Sarr', 'Nabou Diouf', 'Yacine Thiam', 'Seynabou Mbodj',
            'Aïda Kane', 'Diary Touré', 'Binta Wade', 'Oumou Ly',
        ];

        // 80 nouveaux profils féminins (F21–F100)
        $femmes_new = $this->getNewFemaleNames();

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
            $slug = strtolower(str_replace(' ', '.', iconv('UTF-8', 'ASCII//TRANSLIT', $name)));
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

        // Créer les 20 profils féminins originaux (F1–F20)
        foreach ($femmes as $i => $name) {
            $slug = strtolower(str_replace(' ', '.', iconv('UTF-8', 'ASCII//TRANSLIT', $name)));
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

        // Créer les 80 nouveaux profils féminins (F21–F100)
        foreach ($femmes_new as $i => $name) {
            $avatarIndex = $i + 21; // F21 à F100
            $slug = strtolower(str_replace(' ', '.', iconv('UTF-8', 'ASCII//TRANSLIT', $name)));
            // Éviter les doublons d'email
            $email = $slug . '@ugb.edu.sn';
            if (User::where('email', $email)->exists()) {
                $email = $slug . ($i + 21) . '@ugb.edu.sn';
            }
            $user = User::create([
                'name' => $name,
                'email' => $email,
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
                'photo' => $this->findAvatar('F' . $avatarIndex),
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

        echo "✅ 20 hommes + 100 femmes créés + 2 comptes test + 1 match démo\n";
    }

    /**
     * Ajoute uniquement les nouveaux profils féminins (F21–F100)
     * sur une BDD déjà en production. Les profils existants ne sont pas touchés.
     */
    private function addNewFemaleProfiles(): void
    {
        $password = Hash::make('password');
        $ufrs = ['SAT', 'SJP', 'S2ATA', 'LSH', 'SEFS'];
        $levels = ['L1', 'L2', 'L3', 'M1', 'M2'];
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

        $femmes_new = $this->getNewFemaleNames();
        $created = 0;

        foreach ($femmes_new as $i => $name) {
            $avatarIndex = $i + 21; // F21 à F100
            $slug = strtolower(str_replace(' ', '.', iconv('UTF-8', 'ASCII//TRANSLIT', $name)));
            $email = $slug . '@ugb.edu.sn';

            // Vérifier si le profil existe déjà (par email ou par photo)
            if (User::where('email', $email)->exists()) {
                // Vérifier si c'est déjà le bon profil avec la bonne photo
                $existing = User::where('email', $email)->first();
                if ($existing && $existing->profile && str_contains($existing->profile->photo ?? '', 'F' . $avatarIndex)) {
                    continue; // Déjà créé, on passe
                }
                $email = $slug . $avatarIndex . '@ugb.edu.sn';
            }

            // Double vérification par photo pour éviter les doublons
            $photoPath = $this->findAvatar('F' . $avatarIndex);
            if ($photoPath && Profile::where('photo', $photoPath)->exists()) {
                continue; // Ce profil avec cette photo existe déjà
            }

            $user = User::create([
                'name' => $name,
                'email' => $email,
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
                'photo' => $photoPath,
            ]);
            Subscription::createTrial($user->id);
            $created++;
        }

        echo "✅ {$created} nouveaux profils féminins ajoutés (F21–F100)\n";
    }

    private function getNewFemaleNames(): array
    {
        return [
            // F21–F30
            'Rokhaya Dieng', 'Fatoumata Koné', 'Marème Diallo', 'Djena Baldé',
            'Nafi Camara', 'Penda Seck', 'Codou Diatta', 'Kadiatou Bah',
            'Salimata Coulibaly', 'Gnagna Sow',
            // F31–F40
            'Hawa Traoré', 'Ndeye Coumba Diop', 'Maimouna Fall', 'Fanta Keita',
            'Binetou Gning', 'Rougui Ndiaye', 'Thiané Sarr', 'Dienaba Kouyaté',
            'Adama Dembélé', 'Maguette Mbaye',
            // F41–F50
            'Tening Sagna', 'Ndéye Khady Ba', 'Safiatou Barry', 'Djeneba Sylla',
            'Aminata Balde', 'Oumou Konaté', 'Mariam Coulibaly', 'Aissatou Diallo',
            'Khadija Traoré', 'Fatouma Diarra',
            // F51–F60
            'Salamata Ouédraogo', 'Inna Diop', 'Ndeye Aida Sow', 'Khady Diagne',
            'Mame Diarra Ndiaye', 'Sokhna Fatou Diouf', 'Ngoné Ndiaye', 'Bousso Faye',
            'Dior Diagne', 'Fatim Mbaye',
            // F61–F70
            'Ndeye Marème Fall', 'Aminata Sall', 'Rokhaya Mbaye', 'Bigué Ndiaye',
            'Fanta Diop', 'Mariétou Sow', 'Ndèye Bineta Gueye', 'Amy Ndiaye',
            'Ndeye Soda Mbaye', 'Penda Niang',
            // F71–F80
            'Lissa Diallo', 'Ndeye Astou Sarr', 'Ramatoulaye Fall', 'Coumba Lô',
            'Ndèye Fatou Thiam', 'Awa Diallo', 'Seynabou Diop', 'Maty Ndiaye',
            'Fatoumata Diallo', 'Adja Diop',
            // F81–F90
            'Kadiatou Diallo', 'Maimouna Diop', 'Nafi Diallo', 'Marème Sarr',
            'Anta Ndiaye', 'Dieynaba Sow', 'Yaye Fatou Mbaye', 'Aminata Sarr',
            'Thiané Diop', 'Binta Sow',
            // F91–F100
            'Ndeye Coumba Mbaye', 'Fatou Sarr', 'Mame Thioro Ndiaye', 'Rokhaya Sow',
            'Ndèye Maguette Ba', 'Awa Sarr', 'Fatoumata Gaye', 'Khady Ndiaye',
            'Marième Diallo', 'Sokhna Mbacké',
        ];
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