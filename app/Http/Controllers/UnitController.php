<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UnitController extends Controller
{
    public function showPage(Request $request)
    {
        $search = $request->input('search');

        return Inertia::render('Unit', [
            'units' => Unit::query()
                ->when($search != null, fn ($q) => $q->where('name', 'LIKE', "%$search%"))
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function createUnit(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('units', 'name')->whereNull('deleted_at')],
        ]);
        Unit::create($validated);

        return back();
    }

    public function updateUnit($id, Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('units', 'name')->whereNull('deleted_at')],
        ]);
        Unit::findOrFail($id)
            ->update($validated);

        return back();
    }

    public function deleteUnit($id)
    {
        $unit = Unit::findOrFail($id);
        if (! $unit->items()->exists()) {
            $unit->forceDelete();
        } else {
            $unit->delete();
        }

        return back();
    }
}
