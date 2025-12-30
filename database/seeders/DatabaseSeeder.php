<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(20)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        Task::factory()->create([
            'code' => 'LAND_PREP',
            'name' => 'Land Preparation',
            'description' => 'Prepare the land for planting by tilling the soil.',
        ]);

        Task::factory()->create([
            'code' => 'PLANT_CORN',
            'name' => 'Plant Corn Seeds',
            'description' => 'Plant corn seeds in the prepared soil.',
        ]);

        Task::factory()->create([
            'code' => 'PLANT_RICE',
            'name' => 'Plant Rice Seeds',
            'description' => 'Plant rice seeds in the prepared soil.',
        ]);

        Task::factory()->create([
            'code' => 'WATERING',
            'name' => 'Watering',
            'description' => 'Water the plants to ensure they have enough moisture to grow.',
        ]);

        Task::factory()->create([
            'code' => 'HARVESTING',
            'name' => 'Harvesting',
            'description' => 'Harvest the mature crops from the field.',
        ]);

        Task::factory()->create([
            'code' => 'HAVEST_CORN',
            'name' => 'Harvest Corn',
            'description' => 'Harvest the mature corn from the field.',
        ]);

        Task::factory()->create([
            'code' => 'HARVEST_RICE',
            'name' => 'Harvest Rice',
            'description' => 'Harvest the mature rice from the field.',
        ]);
    }
}
