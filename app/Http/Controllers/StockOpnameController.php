<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\OpnameReason;
use Inertia\Inertia;

class StockOpnameController extends Controller
{
    public function form()
    {
        $items = Item::orderBy('name')->get();
        $reasons = OpnameReason::sortedWithOthersAsLast()->get();

        return Inertia::render('OpnameForm', compact('reasons', 'items'));
    }
}
