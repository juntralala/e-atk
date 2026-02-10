<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Requests\UserCreateRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use function PHPUnit\Framework\returnArgument;

class UserController extends Controller
{
    public function showPage(Request $request)
    {
        $users = User::paginate(
            $request->input('per_page', 10),
            page: $request->input('page', 1)
        );

        $users->through(function ($user, $key) use ($users) {
            return array_merge($user->toArray(), [
                'no' => $users->firstItem() + $key
            ]);
        });

        return inertia('User', ['users' => $users]);
    }

    public function create(UserCreateRequest $request)
    {
        $safe = $request->safe();
        User::create([
            'name' => $safe->name,
            'username' => $safe->username,
            'password' => $safe->password,
            'role_id' => $safe->role,
        ]);
        return back();
    }

    public function update(User $user, UserUpdateRequest $request)
    {
        $safe = $request->safe();
        $user->update([
            'name' => $safe->name,
            'username' => $safe->username,
            'role_id' => $safe->role,
        ]);
        if ($safe->password != null) {
            $user->update([
                'password' => $safe->password,
            ]);
        }
        return back();
    }

    public function delete(User $user)
    {
        if ($user == null) {
            return back()->withErrors([
                'message' => 'User tidak ditemukan di database'
            ]);
        }
        $user->delete();
        return back();
    }

    public function showProfile()
    {
        return Inertia::render('Profile', [
            'user' => auth()->user()
        ]);
    }

      public function updateProfile(UpdateProfileRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();

        // Handle profile photo upload
        if ($request->hasFile('profilePhoto')) {
            // Delete old photo if exists
            if (str_replace('/storage', '', $user->profile_photo_path, )) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            // Store new photo
            $path = $request->file('profilePhoto')
                ->store('profile-photos', 'public');
            $validated['profile_photo_path'] = "/storage/$path";
        }

        // Handle password update
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        // Update user
        $user->update($validated);

        return back()->with('success', 'Profile berhasil diperbarui');
    }
}
