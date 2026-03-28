<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\University;
use App\Models\Profile;

class UniversitySeeder extends Seeder
{
    public function run(): void
    {
        $universities = [
            ['name' => 'Université Gaston Berger', 'short_name' => 'UGB', 'city' => 'Saint-Louis', 'region' => 'Saint-Louis'],
            ['name' => 'Université Cheikh Anta Diop', 'short_name' => 'UCAD', 'city' => 'Dakar', 'region' => 'Dakar'],
            ['name' => 'Université Alioune Diop de Bambey', 'short_name' => 'UADB', 'city' => 'Bambey', 'region' => 'Diourbel'],
            ['name' => 'Université Assane Seck de Ziguinchor', 'short_name' => 'UASZ', 'city' => 'Ziguinchor', 'region' => 'Ziguinchor'],
            ['name' => 'Université de Thiès', 'short_name' => 'UT', 'city' => 'Thiès', 'region' => 'Thiès'],
            ['name' => 'Université Iba Der Thiam de Thiès', 'short_name' => 'UIDT', 'city' => 'Thiès', 'region' => 'Thiès'],
            ['name' => 'Université du Sine Saloum El Hadji Ibrahima Niass', 'short_name' => 'USSEIN', 'city' => 'Kaolack', 'region' => 'Kaolack'],
            ['name' => 'Université Amadou Mahtar Mbow', 'short_name' => 'UAM', 'city' => 'Diamniadio', 'region' => 'Dakar'],
            ['name' => 'Institut Supérieur d\'Informatique (ISI)', 'short_name' => 'ISI', 'city' => 'Dakar', 'region' => 'Dakar'],
            ['name' => 'École Supérieure Polytechnique', 'short_name' => 'ESP', 'city' => 'Dakar', 'region' => 'Dakar'],
            ['name' => 'Université Virtuelle du Sénégal', 'short_name' => 'UVS', 'city' => 'Dakar', 'region' => 'Dakar'],
        ];

        foreach ($universities as $uni) {
            University::firstOrCreate(
                ['short_name' => $uni['short_name']],
                $uni
            );
        }

        // Associer les profils existants à l'UGB
        $ugb = University::where('short_name', 'UGB')->first();
        if ($ugb) {
            Profile::whereNull('university_id')
                ->where('university', 'UGB')
                ->update(['university_id' => $ugb->id]);
        }

        $this->command->info('✅ ' . count($universities) . ' universités créées');
    }
}
