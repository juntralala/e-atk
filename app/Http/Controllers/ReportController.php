<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderName;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\BorderWidth;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;

class ReportController
{
    public function itemInOutComparisonPage(Request $request)
    {
        $startMonth = $request->date('start') ?? Date::now('Asia/Makassar')->startOfMonth();
        $endMonth = $request->date('end') ?? Date::now('Asia/Makassar')->endOfMonth();
        $startMonth->startOfMonth();
        $endMonth->endOfMonth();

        return Inertia::render('ItemInOutComparison', [
            'comparisons' => $this->itemInOutComparison($startMonth, $endMonth),
        ]);
    }

    public function itemInOutComparisonXlsx(Request $request)
    {
        $startMonth = $request->date('start') ?? Date::now('Asia/Makassar')->startOfMonth();
        $endMonth = $request->date('end') ?? Date::now('Asia/Makassar')->endOfMonth();
        $startMonth->startOfMonth();
        $endMonth->endOfMonth();

        $comparisons = $this->itemInOutComparison($startMonth, $endMonth);
        $callback = function () use ($comparisons, $startMonth, $endMonth) {
            $options = new Options;
            $options->setColumnWidth(5, 1);
            $options->setColumnWidth(20, 2);
            $options->setColumnWidth(12, 3);
            $options->setColumnWidth(12, 4);
            $options->setColumnWidth(12, 5);
            $options->setColumnWidth(12, 6);

            $writer = new Writer($options);
            $writer->openToFile('php://output');

            $border = new Border(
                new BorderPart(BorderName::LEFT, width: BorderWidth::THIN),
                new BorderPart(BorderName::TOP, width: BorderWidth::THIN),
                new BorderPart(BorderName::RIGHT, width: BorderWidth::THIN),
                new BorderPart(BorderName::BOTTOM, width: BorderWidth::THIN),
            );

            $cellStyle = new Style(
                border: $border,
            );

            $writer->addRow(Row::fromValuesWithStyle([
                'Perbandingan Barang Masuk dan Keluar',
            ], new Style(
                fontBold: true,
                cellAlignment: CellAlignment::CENTER,
                backgroundColor: Color::rgb(255, 237, 206),
            )));
            $options->mergeCells(0, 1, 6, 1, $writer->getCurrentSheet()->getIndex());
            $writer->addRow(Row::fromValuesWithStyle([
                'Periode '.$startMonth->locale('id')->isoFormat('MMMM YYYY').($startMonth->month != $endMonth->month || $startMonth->year != $endMonth->year ? ' - '.$endMonth->locale('id')->isoFormat('MMMM YYYY') : ''),
            ], new Style(
                fontBold: true,
                cellAlignment: CellAlignment::CENTER,
                backgroundColor: Color::rgb(255, 237, 206),
            )));
            $options->mergeCells(0, 2, 6, 2, $writer->getCurrentSheet()->getIndex());

            $writer->addRow(Row::fromValues([]));

            $writer->addRow(Row::fromValuesWithStyle(
                [
                    'No',
                    'Nama Barang',
                    'Bulan',
                    'Satuan',
                    'Masuk',
                    'Keluar',
                    'Selisih',
                ],
                new Style(
                    fontBold: true,
                    border: $border,
                    cellAlignment: CellAlignment::CENTER,
                    cellVerticalAlignment: CellVerticalAlignment::CENTER,
                    backgroundColor: Color::GREEN
                )
            ));

            foreach ($comparisons as $index => $comparison) {
                $writer->addRow(Row::fromValuesWithStyle(
                    [
                        $index + 1,
                        $comparison->item_name,
                        Date::createFromLocaleIsoFormat('YYYY-MM', 'id', $comparison->month)->isoFormat('MMMM YYYY'),
                        $comparison->unit,
                        (int) $comparison->inbound_quantity,
                        (int) $comparison->outbound_quantity,
                        (int) $comparison->difference,
                    ],
                    $cellStyle
                ));
            }

            $writer->close();
        };

        return response()->streamDownload($callback, 'perbandingan-barang-masuk-dan-keluar.xlsx');
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

        $inbound = DB::table('item_addition_details as iad')
            ->join('item_additions as ia', 'ia.id', 'iad.item_addition_id')
            ->select([
                'iad.item_id',
                DB::raw("DATE_FORMAT(CONVERT_TZ(ia.created_at, '+00:00', '+08:00'), '%Y-%m') as month"),
                DB::raw('SUM(iad.quantity) as inbound_quantity'),
                DB::raw('0 as outbound_quantity'),
            ])
            ->whereBetween('ia.created_at', [$startMonth, $endMonth])
            ->groupBy(['iad.item_id', 'month']);

        $outbound = DB::table('item_request_details as ird')
            ->join('item_requests as ir', 'ir.id', 'ird.item_request_id')
            ->select([
                'ird.item_id',
                DB::raw("DATE_FORMAT(CONVERT_TZ(ir.responded_at, '+00:00', '+08:00'), '%Y-%m') as month"),
                DB::raw('0 as inbound_quantity'),
                DB::raw('SUM(ird.responded_quantity) as outbound_quantity'),
            ])
            ->whereBetween('ir.responded_at', [$startMonth, $endMonth])
            ->groupBy(['ird.item_id', 'month']);

        // UNION inbound + outbound, lalu aggregate ulang
        $union = $inbound->unionAll($outbound);

        $data = DB::table('items as i')
            ->join('units as u', 'u.id', 'i.unit_id')
            ->joinSub($union, 'combined', 'combined.item_id', 'i.id')
            ->select([
                'i.name as item_name',
                'u.name as unit',
                'combined.month',
                DB::raw('SUM(combined.inbound_quantity) as inbound_quantity'),
                DB::raw('SUM(combined.outbound_quantity) as outbound_quantity'),
                DB::raw('SUM(combined.inbound_quantity) - SUM(combined.outbound_quantity) as difference'),
            ])
            ->groupBy(['combined.month', 'i.name', 'u.name'])
            ->orderBy('i.name')
            ->orderBy('combined.month')
            ->get();

        return $data->toArray();
    }
}
