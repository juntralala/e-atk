<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function showPage()
    {
        $users = User::whereHas('role', function ($query) {
            $query->where('name', 'petugas');
        })->get();

        /** @param User */
        $users = $users->map(function ($user) {
            $user =  $user->toArray();
            $user['role'] = $user['role']['name'];
            return $user;
        });
        
        return inertia('LandingPage', [
            'petugasUsers' => $users
        ]);
    }
}
