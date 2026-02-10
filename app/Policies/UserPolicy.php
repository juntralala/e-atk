<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function __construct()
    {
        $this->permittedRoles = collect([
            'administrator',
            'bendahara'
        ]);
    }

    private $permittedRoles;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, User $model): bool
    {
        return true;
    }

    public function create(User $user): Response
    {
        $isCan = false;
        $isCan = $this->permittedRoles->some(fn($role) => $user->role->name == $role);
        return $isCan
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses untuk membuat user.');
    }

    public function update(User $user, User $model): Response
    {
        $canUpdate = $user->id == $model->id || $this->permittedRoles->some(fn($role) => $user->role->name == $role);

        return $canUpdate
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses untuk mengubah user ini.');
    }

    public function delete(User $user, User $model): Response
    {
        $canDelete = $user->id == $model->id || $this->permittedRoles->some(fn($role) => $user->role->name == $role);
        return $canDelete
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses untuk menghapus user ini.');
    }

    public function restore(User $user, User $model): Response
    {
        return $this->permittedRoles->some(fn($role) => $user->role->name == $role)
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses untuk mengembalikan user ini.');
    }

    public function forceDelete(User $user, User $model): Response
    {
        return $this->permittedRoles->some(fn($role) => $user->role->name == $role)
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses untuk menghapus permanen user ini.');
    }
}