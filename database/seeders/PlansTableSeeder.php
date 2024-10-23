<?php

namespace Database\Seeders;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlansTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Plan::create(
            [
                'name' => 'Free Plan',
                'price' => 0,
                'duration' => 'Lifetime',
                'max_employee' => 5,
                'max_client' => 5,
                'image' => 'free_plan.png',
                'storage_limit' => 1024,
                'enable_chatgpt' => 'on'
                ]
        );
    }
}
