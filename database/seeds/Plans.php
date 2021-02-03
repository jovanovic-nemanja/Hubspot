<?php

use Illuminate\Database\Seeder;
use App\Models\Plans as mPlans;

class Plans extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        mPlans::create([
            'name' => 'Free',
            'type' => 'free',
            'portal_limit' => 1,
            'user_limit' => 1,
            'module_limit' => 10,
            'is_free_module' => 'yes',
            'is_hidden_plan' => 'no'
        ]);

        mPlans::create([
            'name' => 'Sprocket Rocket PRO',
            'type' => 'fixed',
            'portal_limit' => 10,
            'user_limit' => 10,
            'module_limit' => 999,
            'is_free_module' => 'no',
            'is_hidden_plan' => 'yes',
            'price' => 100,
            'currency' => 'USD'
        ]);
    }
}
