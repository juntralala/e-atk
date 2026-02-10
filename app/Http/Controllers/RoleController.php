<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function getRoles()
    {
        $roles = [];
        $namaRole = auth()->user()->role->nama;
        if($namaRole == 'administrator') {
            $roles = Role::whereNotIn('nama', ['administrator', 'super admin'])->get();
        } else {
            $roles = Role::get();
        }
        return [
            'data' => $roles
        ];
    }
}
