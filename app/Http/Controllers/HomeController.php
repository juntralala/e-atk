<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    private array $routes = [
        'unit' => 'items.requests.form',
        'petugas' => 'items.requests',
        'bendahara' => 'items.requests',
        'administrator' => 'users',
    ];

    public function redirector(Request $request)
    {
        $role = $request->user()->role->name;
        $url = route($this->routes[$role] ?? 'dashboard');

        return redirect($url);
    }
}
