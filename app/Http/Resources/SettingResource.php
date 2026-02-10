<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'logo' => $this->logo ?? null,
            'icon' => $this->icon ?? null,
            'applicationName' => $this->application_name ?? "Belum ada nama aplikasi",
            'institutionName' => $this->institution_name ?? "Belum ada nama instansni",
            'institutionAddress' => $this->institution_address ?? "Belum ada alamat",
            'institutionPhone' => $this->institution_phone ?? "Belum ada nomer telepon",
            'moto' => 'Kami himung, pian wigas',
            'visi' => 'Terwujudnya rumah sakit yang paripurna, bermutu dan berorientasi pada kepuasan pasien.',
            'misi' => [
                "Meningkatkan mutu pelayanan dan keselamatan pasien dengan memberikan pelayanan yang profesional.",
                "Menjadikan lingkungan rumah sakit yang tertata, bersih dan rapi.",
                "Meningkatkan profesionalisme sumber daya manusia dalam memberikan layanan yang berkualitas.",
                "Mewujudkan sarana dan prasarana yang mendukung peningkatan pelayanan.",
                "Meningkatkan kepedulian rumah sakit terhadap kepuasan Pasien dan kesejahteraan karyawan."
            ]
        ];
    }
}
