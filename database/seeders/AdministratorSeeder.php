<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdministratorSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('name', 'administrator')->firstOrFail();
        User::create([
            'name' => 'Muhammad Junaidi',
            'username' => 'junaidi',
            'role_id' => $role->id,
            'password' => '1234',
        ]);
    }
}
