<?php

namespace App\Http\Controllers;

use App\Models\OpnameReason;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class OpnameReasonController extends Controller
{
    public function showPage()
    {
        $reasons = OpnameReason::get();

        return Inertia::render('OpnameReason', compact('reasons'));
    }

    public function create(Request $request)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:100', Rule::unique('opname_reasons', 'reason')->whereNull('deleted_at')],
        ]);
        OpnameReason::create($data);

        return back();
    }

    public function update($id, Request $request)
    {
        $reason = OpnameReason::findOrFail($id);
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:100', Rule::unique('opname_reasons', 'reason')->whereNull('deleted_at')],
        ]);
        $reason->update($data);

        return back();
    }

    public function delete($id)
    {
        $reason = OpnameReason::findOrFail($id);
        if ($reason->stockOpnameDetails()->exists()) {
            $reason->delete();
        } else {
            $reason->forceDelete();
        }

        return back();
    }
}
