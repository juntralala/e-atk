<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockAdjustmentCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:65535'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.adjustment' => ['required', 'numeric'],
            'items.*.reason_id' => ['required', 'exists:stock_adjustment_reasons,id'],
        ];
    }
}
