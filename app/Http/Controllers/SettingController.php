<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function showPage()
    {
        return Inertia::render('Setting');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'applicationName' => 'required|string|max:255',
            'institutionName' => 'required|string|max:255',
            'institutionAddress' => 'required|string|max:255',
            'institutionPhone' => 'required|string|max:255',
        ], [
            'icon.image' => 'File icon harus berupa gambar.',
            'icon.mimes' => 'Icon harus berformat: jpeg, png, jpg, gif, atau svg.',
            'icon.max' => 'Ukuran icon maksimal 2MB.',
            'logo.image' => 'File logo harus berupa gambar.',
            'logo.mimes' => 'Logo harus berformat: jpeg, png, jpg, gif, atau svg.',
            'logo.max' => 'Ukuran logo maksimal 2MB.',
            'applicationName.required' => 'Nama aplikasi wajib diisi.',
            'institutionName.required' => 'Nama instansi wajib diisi.',
            'institutionAddress.required' => 'Alamat instansi wajib diisi.',
            'institutionPhone.required' => 'Telepon instansi wajib diisi.',
        ]);

        // Ambil atau buat setting pertama kali
        $setting = Setting::first();

        if (! $setting) {
            $setting = new Setting;
        }

        $data = [
            'application_name' => $validated['applicationName'],
            'institution_name' => $validated['institutionName'],
            'institution_address' => $validated['institutionAddress'],
            'institution_phone' => $validated['institutionPhone'],
        ];

        // Handle icon upload
        if ($request->hasFile('icon')) {
            // Hapus icon lama jika ada
            if ($setting->icon) {
                $oldIconPath = str_replace('/storage/', '', $setting->icon);
                if (Storage::disk('public')->exists($oldIconPath)) {
                    Storage::disk('public')->delete($oldIconPath);
                }
            }

            $iconPath = $request->file('icon')->store('settings/icons', 'public');
            $data['icon'] = "/storage/$iconPath";
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Hapus logo lama jika ada
            if ($setting->logo) {
                $oldLogoPath = str_replace('/storage/', '', $setting->logo);
                if (Storage::disk('public')->exists($oldLogoPath)) {
                    Storage::disk('public')->delete($oldLogoPath);
                }
            }

            $logoPath = $request->file('logo')->store('settings/logos', 'public');
            $data['logo'] = "/storage/$logoPath";
        }

        // Update atau create
        if ($setting->exists) {
            $setting->update($data);
        } else {
            $setting->fill($data);
            $setting->save();
        }

        return redirect()->back();
    }
}
