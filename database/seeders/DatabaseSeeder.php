<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Profile;
use App\Models\Like;
use App\Models\Matche;
use App\Models\Message;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Prénoms sénégalais réalistes.
     */
    private array $prenomsHommes = [
        'Moussa', 'Ibrahima', 'Ousmane', 'Abdoulaye', 'Mamadou',
        'Cheikh', 'Modou', 'Pape', 'Aliou', 'Babacar',
        'Samba', 'Daouda', 'Lamine', 'Amadou', 'Thierno',
        'El Hadji', 'Malick', 'Boubacar', 'Assane', 'Souleymane',
    ];

    private array $prenomsFemmes = [
        'Aminata', 'Fatou', 'Aissatou', 'Mariama', 'Ndèye',
        'Khady', 'Awa', 'Coumba', 'Dieynaba', 'Rama',
        'Sokhna', 'Mame', 'Bintou', 'Adja', 'Yacine',
        'Astou', 'Maimouna', 'Ndeye', 'Rokhaya', 'Diary',
    ];

    private array $noms = [
        'Diop', 'Ndiaye', 'Fall', 'Ba', 'Sow',
        'Diallo', 'Sy', 'Mbaye', 'Gueye', 'Niang',
        'Kane', 'Sarr', 'Cissé', 'Touré', 'Ly',
        'Thiam', 'Faye', 'Wade', 'Diouf', 'Seck',
    ];

    private array $ufrs = ['SAT', 'SJP', 'LSH', 'S2ATA', 'SEFS'];

    private array $levels = ['L1', 'L2', 'L3', 'M1', 'M2'];

    private array $promotions = ['P28', 'P29', 'P30', 'P31', 'P32'];

    private array $interets = [
        'Sport', 'Musique', 'Cinéma', 'Voyage', 'Lecture',
        'Jeux vidéo', 'Cuisine', 'Tech', 'Danse', 'Football',
        'Photographie', 'Mode', 'Poésie', 'Entrepreneuriat',
    ];

    private array $bios = [
        "Passionné(e) de tech et de café ☕. Toujours partant(e) pour une discussion intéressante.",
        "Étudiant(e) motivé(e), cherche quelqu'un avec qui partager des moments cool sur le campus 😊",
        "Fan de musique et de bons plats. Viens, on explore Saint-Louis ensemble !",
        "La vie est trop courte pour s'ennuyer. On se retrouve à la bibliothèque ? 📚",
        "Sportif/ve le jour, cinéphile la nuit. Ton prochain partenaire de révision ?",
        "En quête de belles rencontres et de discussions profondes 💭",
        "J'aime les défis, le football et les thiébou djeun de ma mère 🍚",
        "Créatif/ve dans l'âme. Musique, art et bonne compagnie, c'est ma recette du bonheur.",
        "UGB m'a tout donné sauf un(e) partenaire de révision... À toi de jouer !",
        "Simple, souriant(e) et toujours de bonne humeur. Viens découvrir 😉",
    ];

    public function run(): void
    {
        // ── Compte de test ──
        $testUser = User::factory()->create([
            'name'     => 'Moussa Test',
            'email'    => 'test@ugb.edu.sn',
            'password' => Hash::make('password'),
        ]);

        Profile::create([
            'user_id'        => $testUser->id,
            'age'            => 22,
            'gender'         => 'homme',
            'ufr'            => 'SAT',
            'promotion'      => 'P30',
            'field_of_study' => 'Informatique',
            'level'          => 'L3',
            'bio'            => 'Compte de test - Développeur en herbe 🚀',
            'interests'      => 'Tech,Sport,Musique',
            'university'     => 'UGB',
        ]);

        // ── Compte de test femme ──
        $testFemme = User::factory()->create([
            'name'     => 'Aminata Test',
            'email'    => 'aminata@ugb.edu.sn',
            'password' => Hash::make('password'),
        ]);

        Profile::create([
            'user_id'        => $testFemme->id,
            'age'            => 21,
            'gender'         => 'femme',
            'ufr'            => 'SAT',
            'promotion'      => 'P30',
            'field_of_study' => 'Mathématiques',
            'level'          => 'L3',
            'bio'            => 'Compte de test femme - Amoureuse des maths 📐',
            'interests'      => 'Lecture,Tech,Voyage',
            'university'     => 'UGB',
        ]);

        // ── Générer 20 hommes ──
        foreach ($this->prenomsHommes as $i => $prenom) {
            $nom = $this->noms[array_rand($this->noms)];
            $user = User::factory()->create([
                'name'  => "$prenom $nom",
                'email' => strtolower($prenom) . '.' . strtolower($nom) . ($i + 1) . '@ugb.edu.sn',
            ]);

            Profile::create([
                'user_id'        => $user->id,
                'age'            => rand(18, 28),
                'gender'         => 'homme',
                'ufr'            => $this->ufrs[array_rand($this->ufrs)],
                'promotion'      => $this->promotions[array_rand($this->promotions)],
                'field_of_study' => 'Non précisé',
                'level'          => $this->levels[array_rand($this->levels)],
                'bio'            => $this->bios[array_rand($this->bios)],
                'interests'      => $this->randomInterests(),
                'university'     => 'UGB',
            ]);
        }

        // ── Générer 20 femmes ──
        foreach ($this->prenomsFemmes as $i => $prenom) {
            $nom = $this->noms[array_rand($this->noms)];
            $user = User::factory()->create([
                'name'  => "$prenom $nom",
                'email' => strtolower($prenom) . '.' . strtolower($nom) . ($i + 1) . '@ugb.edu.sn',
            ]);

            Profile::create([
                'user_id'        => $user->id,
                'age'            => rand(18, 26),
                'gender'         => 'femme',
                'ufr'            => $this->ufrs[array_rand($this->ufrs)],
                'promotion'      => $this->promotions[array_rand($this->promotions)],
                'field_of_study' => 'Non précisé',
                'level'          => $this->levels[array_rand($this->levels)],
                'bio'            => $this->bios[array_rand($this->bios)],
                'interests'      => $this->randomInterests(),
                'university'     => 'UGB',
            ]);
        }

        // ── Créer quelques likes et matchs de test ──
        // Le compte test homme like quelques femmes
        $femmes = User::whereHas('profile', fn($q) => $q->where('gender', 'femme'))
            ->limit(5)
            ->get();

        foreach ($femmes as $femme) {
            Like::create([
                'user_id'       => $testUser->id,
                'liked_user_id' => $femme->id,
            ]);
        }

        // Le compte test femme like le test homme → match !
        Like::create([
            'user_id'       => $testFemme->id,
            'liked_user_id' => $testUser->id,
        ]);

        $match = Matche::create([
            'user1_id' => min($testUser->id, $testFemme->id),
            'user2_id' => max($testUser->id, $testFemme->id),
        ]);

        // Quelques messages de test
        $messages = [
            [$testFemme->id, 'Salut ! Comment tu vas ? 😊'],
            [$testUser->id, 'Hey ! Ça va bien et toi ? Content du match !'],
            [$testFemme->id, 'Très bien merci ! Tu es en quelle filière ?'],
            [$testUser->id, 'Je suis en L3 Info à la SAT. Et toi ?'],
            [$testFemme->id, 'Maths appliquées ! On est voisins de département alors 😄'],
        ];

        foreach ($messages as $i => $msg) {
            Message::create([
                'match_id'   => $match->id,
                'sender_id'  => $msg[0],
                'message'    => $msg[1],
                'created_at' => now()->subMinutes(count($messages) - $i),
            ]);
        }

        $this->command->info('✅ Seed terminé : 42 utilisateurs, 1 match, 5 messages');
        $this->command->info('📧 Compte test homme : test@ugb.edu.sn / password');
        $this->command->info('📧 Compte test femme : aminata@ugb.edu.sn / password');
    }

    /**
     * Retourne 2-4 intérêts aléatoires séparés par des virgules.
     */
    private function randomInterests(): string
    {
        $keys = array_rand($this->interets, rand(2, 4));
        $selected = array_map(fn($k) => $this->interets[$k], (array) $keys);

        return implode(',', $selected);
    }
}
