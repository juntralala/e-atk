<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemRequestCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.requested_quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'request_date.required' => 'Tanggal permintaan harus diisi.',
            'request_date.date' => 'Tanggal permintaan tidak valid.',
            'items.required' => 'Minimal harus ada satu barang yang diminta.',
            'items.array' => 'Format data barang tidak valid.',
            'items.min' => 'Minimal harus ada satu barang yang diminta.',
            'items.*.item_id.required' => 'Barang harus dipilih.',
            'items.*.item_id.exists' => 'Barang yang dipilih tidak valid.',
            'items.*.requested_quantity.required' => 'Jumlah barang harus diisi.',
            'items.*.requested_quantity.integer' => 'Jumlah barang harus berupa angka.',
            'items.*.requested_quantity.min' => 'Jumlah barang minimal 1.',
        ];
    }

    public function attributes(): array
    {
        return [
            'request_date' => 'tanggal permintaan',
            'items' => 'barang',
            'items.*.item_id' => 'barang',
            'items.*.requested_quantity' => 'jumlah diminta',
        ];
    }
}