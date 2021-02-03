<?php

use Illuminate\Database\Seeder;
use App\Models\Agencies as mAgencies;
use App\Models\Plans as mPlans;
use App\User;
use Spatie\Permission\Models\Role;

class Agencies extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $agency = mAgencies::create(['name' => 'Lean Labs', 'cover' => '', 'status' => 'active', 'admin_name' => 'Sprocket Rocket', 'admin_email' => 'hi@sprocketrocket.co', 'paid_in_advance' => 1]);

        $plans = mPlans::where('id', 2)->firstOrFail();

        $agency->plans()->attach($plans->id);

        $user = User::create([
            'name' => 'Sprocket Rocket',
            'email' => 'hi@sprocketrocket.co',
            'password' => bcrypt('123456')
        ]);

        $role = Role::where('name', 'agency-admin')->firstOrFail();

        $user->assignRole($role);
        $agency->users()->save($user);
    }
}
