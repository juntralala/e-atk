<?php

namespace App\Http\Controllers;

use App\Models\StockAdjustmentReason;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class StockAdjustmentReasonController extends Controller
{
    public function showPage()
    {
        $reasons = StockAdjustmentReason::get();

        return Inertia::render('StockAdjustmentReason', compact('reasons'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:100', Rule::unique('stock_adjustment_reasons')->whereNull('deleted_at')],
        ]);

        $reason = $request->string('reason');
        StockAdjustmentReason::create([
            'reason' => $reason,
        ]);

        return back()->with([
            'message' => 'success',
        ]);
    }

    public function update(Request $request, $id)
    {
        $stockAdjustmentReason = StockAdjustmentReason::findOrFail($id);
        $request->validate([
            'reason' => ['required', 'string', 'max:100', Rule::unique('reason')->whereNull('deleted_at')],
        ]);
        $reason = $request->string('reason');
        $stockAdjustmentReason->update([
            'reason' => $reason,
        ]);

        return back()->with([
            'message' => 'success',
        ]);
    }

    public function delete($id)
    {
        $stockAdjustmentReason = StockAdjustmentReason::findOrFail($id, ['id']);
        $stockAdjustmentReason->delete();

        return back()->with([
            'message' => 'success',
        ]);
    }
}
