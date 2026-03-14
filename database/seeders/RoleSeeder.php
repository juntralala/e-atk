<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $namaRoles = [
            'administrator',
            'bendahara',
            'petugas',
            'unit',
        ];
        foreach ($namaRoles as $namaRole) {
            Role::create([
                'name' => $namaRole,
            ]);
        }
    }
}
