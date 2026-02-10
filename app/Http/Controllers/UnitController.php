<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Inertia\Inertia;

class UnitController extends Controller
{
    public function showPage()
    {
        return Inertia::render('Unit', [
            'units' => Unit::get()
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
        if ($unit->items()->exists()) {
            $unit->forceDelete();
        } else {
            $unit->delete();
        }
        return back();
    }
}
