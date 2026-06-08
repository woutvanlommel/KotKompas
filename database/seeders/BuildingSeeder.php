<?php

namespace Database\Seeders;

use App\Models\Building;
use Illuminate\Database\Seeder;

class BuildingSeeder extends Seeder
{
    public function run(): void
    {
        Building::create([
            'landlord_id' => 2,
            'name' => 'Kot Kuringstraat',
            'description' => 'Studentenkot met 8 kamers in het centrum van Hasselt',
            'street' => 'Kuringstraat',
            'house_number' => 15,
            'postal_code' => 3500,
            'city' => 'Hasselt',
            'country' => 'BE',
        ]);

        Building::create([
            'landlord_id' => 2,
            'name' => 'Studentenkot Gouverneurslaan',
            'description' => 'Modern studentenhuisvesting met 12 kamers en gedeelde voorzieningen',
            'street' => 'Gouverneurslaan',
            'house_number' => 42,
            'postal_code' => 3500,
            'city' => 'Hasselt',
            'country' => 'BE',
        ]);

        Building::create([
            'landlord_id' => 2,
            'name' => 'Kot Schouwbroekstraat',
            'description' => 'Gezellige studentenkot met 6 kamers nabij het station',
            'street' => 'Schouwbroekstraat',
            'house_number' => 88,
            'postal_code' => 3500,
            'city' => 'Hasselt',
            'country' => 'BE',
        ]);
    }
}
