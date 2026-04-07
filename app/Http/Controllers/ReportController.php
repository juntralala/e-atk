<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController
{

    public function itemInOutComparisonPage(Request $request) {
        $startMonth = $request->date("start") ?? Date::now('Asia/Makassar')->startOfMonth();
        $endMonth = $request->date("end") ?? Date::now('Asia/Makassar')->endOfMonth();
        $startMonth->startOfMonth();
        $endMonth->endOfMonth();

        return Inertia::render("ItemInOutComparison", [
            'comparisons' => $this->itemInOutComparison($startMonth, $endMonth)
        ]);
    }


    public function itemInOutComparison($startMonth, $endMonth)
    {

        // $data = DB::table("items as i")
        //     ->join("units as u", "u.id", "i.unit_id")
        //     ->join("item_addition_details as iad", "iad.item_id", "i.id")
        //     ->join("item_additions as ia", "ia.id", "iad.item_addition_id")
        //     ->join("item_request_details as ird", "ird.item_id", "i.id")
        //     ->join("item_requests as ir", "ir.id", "ird.item_request_id")
        //     ->select([
        //         "i.name",
        //         "u.name as unit",
        //         DB::raw("DATE_FORMAT(CONVERT_TZ(ird.created_at, '+00:00', '+08:00'), '%Y-%m') as month"),
        //         DB::raw("SUM(iad.quantity) as inbound_quantity"),
        //         DB::raw("SUM(ird.responded_quantity) as outbound_quantity"),
        //     ])
        //     ->whereBetween("ia.created_at", [$startMonth, $endMonth])
        //     ->orWhereBetween("ir.responded_at", [$startMonth, $endMonth])
        //     ->groupBy(['month', 'i.name', 'unit'])
        //     ->get();

        $inbound = DB::table("item_addition_details as iad")
            ->join("item_additions as ia", "ia.id", "iad.item_addition_id")
            ->select([
                "iad.item_id",
                DB::raw("DATE_FORMAT(CONVERT_TZ(ia.created_at, '+00:00', '+08:00'), '%Y-%m') as month"),
                DB::raw("SUM(iad.quantity) as inbound_quantity"),
                DB::raw("0 as outbound_quantity"),
            ])
            ->whereBetween("ia.created_at", [$startMonth, $endMonth])
            ->groupBy(["iad.item_id", "month"]);


        $outbound = DB::table("item_request_details as ird")
            ->join("item_requests as ir", "ir.id", "ird.item_request_id")
            ->select([
                "ird.item_id",
                DB::raw("DATE_FORMAT(CONVERT_TZ(ir.responded_at, '+00:00', '+08:00'), '%Y-%m') as month"),
                DB::raw("0 as inbound_quantity"),
                DB::raw("SUM(ird.responded_quantity) as outbound_quantity"),
            ])
            ->whereBetween("ir.responded_at", [$startMonth, $endMonth])
            ->groupBy(["ird.item_id", "month"]);

        // UNION inbound + outbound, lalu aggregate ulang
        $union = $inbound->unionAll($outbound);

        $data = DB::table("items as i")
            ->join("units as u", "u.id", "i.unit_id")
            ->joinSub($union, "combined", "combined.item_id", "i.id")
            ->select([
                "i.name as item_name",
                "u.name as unit",
                "combined.month",
                DB::raw("SUM(combined.inbound_quantity) as inbound_quantity"),
                DB::raw("SUM(combined.outbound_quantity) as outbound_quantity"),
                DB::raw("SUM(combined.inbound_quantity) - SUM(combined.outbound_quantity) as difference"),
            ])
            ->groupBy(["combined.month", "i.name", "u.name"])
            ->orderBy("i.name")
            ->orderBy("combined.month")
            ->get();


        return $data->toArray();
    }

}
