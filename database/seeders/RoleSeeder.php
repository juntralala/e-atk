<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        foreach($namaRoles as $namaRoles) {
            Role::create([
                'name' => $namaRoles
            ]);
        }
    }
}
