<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemAddition;
use App\Models\ItemRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function dashboardPage()
    {
        return inertia('Dashboard');
    }

    public function summary()
    {
        return response()->json([
            'totalItems' => (int) Item::sum('stock'),
            'totalRequests' => ItemRequest::count(),
            'pendingRequests' => ItemRequest::where('status', 'pending')->count(),
            'totalAdditions' => (int) ItemAddition::withSum('itemAdditionDetails as total', 'quantity')->first()?->total,
        ]);
    }

    public function monthlyExpenditures()
    {
        DB::listen(fn ($q) => Log::info($q->sql));
        $endDate = Date::now()->timezone('+8')->endOfMonth();
        $startDate = Date::now()->timezone('+8')->subMonths(11)->startOfMonth();

        $data = DB::table('item_requests as ir')
            ->select([
                DB::raw('MONTH(ir.responded_at) as month'),
                DB::raw('SUM(ird.price * ird.responded_quantity) as totalPrice'),
                // DB::raw('SUM(ird.responded_quantity)'),
            ])
            ->where('status', 'accepted')
            ->whereBetween('ir.responded_at', [$startDate, $endDate])
            ->leftJoin('item_request_details as ird', 'ir.id', '=', 'ird.item_request_id')
            ->groupBy('month')
            ->get()
            ->keyBy(fn ($item) => $item->month);

        $monthly = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Date::now()->timezone('+8')->subMonths($i)->locale('id_ID');
            $key = $date->month;
            $monthly[] = [
                'month' => $date->isoFormat('MMM YYYY'),
                'totalValue' => isset($data[$key]) ? (float) $data[$key]->totalPrice : 0,
            ];
        }

        return response()->json(['data' => $monthly]);
    }

    public function expendituresPerItems()
    {
        $start = Date::now()->timezone('+8')->startOfMonth();
        $end = Date::now()->timezone('+8')->endOfMonth();

        $result = DB::table('items', 'i')
            ->leftJoin('item_request_details as ird', 'i.id', '=', 'ird.item_id')
            ->leftJoin('item_requests as ir', 'ird.item_request_id', '=', 'ir.id')
            ->select([
                DB::raw("CONCAT(i.name, ' - ', i.specification_name) as item_title"),
                DB::raw('SUM(ird.responded_quantity * ird.price) as total_price'),
            ])
            ->orderBy('item_title')
            ->groupBy('item_title')
            ->whereBetween('responded_at', [$start, $end])
            ->get();
        $expenditures = $result->map(
            fn ($item) => [
                'name' => $item->item_title,
                'totalPrice' => $item->total_price,
            ]
        );

        return response()->json([
            'data' => $expenditures,
        ]);
    }

    public function expenditurePerUnit()
    {
        $start = Date::now()->timezone('+8')->startOfMonth();
        $end = Date::now()->timezone('+8')->endOfMonth();

        $result = DB::table('users as u')
            ->leftJoin('roles', 'role_id', 'roles.id')
            ->leftJoin('item_requests as ir', 'u.id', '=', 'ir.requester_id')
            ->leftJoin('item_request_details as ird', 'ir.id', '=', 'ird.item_request_id')
            ->select([
                DB::raw('u.name'),
                DB::raw('SUM(ird.responded_quantity * ird.price) as totalPrice'),
            ])
            ->where('roles.name', 'unit')
            ->whereBetween('responded_at', [$start, $end])
            ->where('ir.status', 'accepted')
            ->orderBy('u.name')
            ->groupBy('u.name')
            ->get();

        return response()->json([
            'data' => $result,
        ]);
    }

    public function topItems(Request $request)
    {
        $start = Date::now()->timezone('+8')->startOfMonth();
        $end = Date::now()->timezone('+8')->endOfMonth();

        $result = DB::table('items as i')
            ->join('item_request_details as ird', 'i.id', '=', 'ird.item_id')
            ->join('item_requests as ir', 'ird.item_request_id', '=', 'ir.id')
            ->select([
                'i.name',
                DB::raw('SUM(ird.responded_quantity) as quantity'),
            ])
            ->whereBetween('ir.responded_at', [$start, $end])
            ->where('status', 'accepted')
            ->orderBy('quantity', 'desc')
            ->groupBy('i.name')
            ->limit($request->integer('length', 5))
            ->get();

        return response()->json([
            'data' => $result,
        ]);
    }

    public function recentItemRequests()
    {
        $itemRequests = ItemRequest::with([
            'requester:id,name',
            'itemRequestDetails:id,item_request_id,responded_quantity,requested_quantity,item_id',
            'itemRequestDetails.item:id,name,unit_id',
            'itemRequestDetails.item.unit:id,name',
        ])
            ->limit(5)
            ->orderBy('created_at', 'desc')
            ->get();
        $itemRequests = $itemRequests->map(fn ($itemRequest) => [
            'id' => $itemRequest->id,
            'requester' => $itemRequest->requester->name,
            'status' => $itemRequest->status,
            'date' => $itemRequest->created_at->isoFormat('DD-MM-YYYY'),
            'items' => $itemRequest->itemRequestDetails->map(fn ($detail) => [
                'name' => $detail->item->name,
                'unit' => $detail->item->unit->name,
                'quantity' => ((bool) $detail->responded_quantity) ? $detail->responded_quantity : $detail->requested_quantity,
            ]),
        ]);

        return response()->json([
            'data' => $itemRequests,
        ]);
    }

    public function itemRequestStatuses()
    {
        $start = Date::now()->timezone('+8')->startOfYear();
        $end = Date::now()->timezone('+8')->endOfYear();

        $result = ItemRequest::select(['status', DB::raw('COUNT(*) as count')])
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('status')
            ->get();

        return response()->json([
            'data' => $result->map(fn ($item) => [
                'status' => __($item->status),
                'count' => $item->count,
            ]),
        ]);
    }
}
