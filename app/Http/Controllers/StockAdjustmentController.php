<?php

namespace App\Http\Controllers;

use App\Actions\StockAdjustment\StockAdjustmentWithApprovalAction;
use App\Actions\StockAdjustment\StockAdjustmentWithoutApprovalAction;
use App\Enums\StockAdjustmentStatus;
use App\Http\Requests\StockAdjustmentCreateRequest;
use App\Models\Item;
use App\Models\Setting;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentReason;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Inertia\Inertia;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderName;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\BorderStyle;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;

class StockAdjustmentController extends Controller
{
    public function form()
    {
        return Inertia::render('StockAdjustmentForm', [
            'items' => Item::get(),
            'reasons' => StockAdjustmentReason::get(),
        ]);
    }

    public function create(
        StockAdjustmentCreateRequest $request,
        StockAdjustmentWithoutApprovalAction $stockAdjustmentWithoutApprovalAction,
        StockAdjustmentWithApprovalAction $stockAdjustmentWithApprovalAction,
    ) {
        $validated = $request->validated();
        if ($this->isStockAdjustmentApprovalActive()) {
            $stockAdjustmentWithApprovalAction->handle($validated);
        } else {
            $stockAdjustmentWithoutApprovalAction->handle($validated);
        }

        return back()->with([
            'message' => 'success',
        ]);
    }

    public function stockAdjustmentReport(Request $request)
    {
        $start = $request->date('start', tz: '+8') ?? Date::now('+8');
        $end = $request->date('end', tz: '+8') ?? Date::now('+8');
        $start->setTimezone('+8');
        $end->setTimezone('+8');
        $start->startOfDay();
        $end->endOfDay();
        $start->setTimezone('+0');
        $end->setTimezone('+0');
        $page = $request->integer('page', 1);

        return Inertia::render('StockAdjustmentReport', [
            'stockAdjustments' => StockAdjustment::with([
                'user',
                'responder',
                'details',
                'details.item',
                'details.item.unit',
            ])->whereBetween('created_at', [$start, $end])
                ->orderByDesc('created_at')
                ->paginate(10, page: $page),
            'reasons' => StockAdjustmentReason::get(),
        ]);
    }

    private function isStockAdjustmentApprovalActive(): bool
    {
        return Setting::select(['stock_adjustment_approval'])->first()?->stock_adjustment_approval ?? false;
    }

    public function toXlsx(Request $request)
    {
        $start = $request->date('start', tz: '+8') ?? Date::now('+8');
        $end = $request->date('end', tz: '+8') ?? Date::now('+8');
        $start->setTimezone('+8');
        $end->setTimezone('+8');
        $start->startOfDay();
        $end->endOfDay();
        $start->setTimezone('+0');
        $end->setTimezone('+0');

        $callback = function () use ($start, $end) {
            $options = new Options;
            $colIndex = 1;
            $options->setColumnWidth(4, $colIndex++); // no
            $options->setColumnWidth(12, $colIndex++); // Tanggal
            $options->setColumnWidth(12, $colIndex++); // Ref. Opname
            $options->setColumnWidth(13, $colIndex++); // Penyesuaian Barang - Nama Barang
            $options->setColumnWidth(12, $colIndex++); // Penyesuaian Barang - Penyesuaian
            $options->setColumnWidth(12, $colIndex++); // Penyesuaian Barang - Old Stok
            $options->setColumnWidth(12, $colIndex++); // Penyesuaian Barang - New Stok
            $options->setColumnWidth(8, $colIndex++);  // Penyesuaian Barang - Satuan
            $options->setColumnWidth(18, $colIndex++); // Diajukan Oleh
            $options->setColumnWidth(18, $colIndex++); // Ditinjau Oleh
            $options->setColumnWidth(20, $colIndex++); // Catatan

            $writer = new Writer($options);
            $writer->openToFile('php://output');
            $line = 1;

            $border = new Border(
                new BorderPart(BorderName::LEFT, style: BorderStyle::SOLID),
                new BorderPart(BorderName::TOP, style: BorderStyle::SOLID),
                new BorderPart(BorderName::BOTTOM, style: BorderStyle::SOLID),
                new BorderPart(BorderName::RIGHT, style: BorderStyle::SOLID)
            );

            $headerStyle = new Style(
                fontBold: true,
                cellAlignment: CellAlignment::CENTER,
                cellVerticalAlignment: CellVerticalAlignment::CENTER,
                border: $border,
                backgroundColor: '00B054'
            );

            $bordered = new Style()
                ->withBorder($border);

            $boldCenter = new Style(
                fontBold: true,
                cellAlignment: CellAlignment::CENTER
            );

            $options->mergeCells(0, 1, 9, 1);
            $writer->addRow(Row::fromValuesWithStyle([
                'UPTD RSUD Haji Darlan Ismail',
            ], $boldCenter));
            $options->mergeCells(0, 2, 9, 2);
            $writer->addRow(Row::fromValuesWithStyle([
                'Laporan Penyesuaian Stok Barang',
            ], $boldCenter));
            $options->mergeCells(0, 3, 9, 3);
            $writer->addRow(Row::fromValuesWithStyle([
                'Periode '.$start->isoFormat('DD-MM-YYYY').' - '.$end->isoFormat('DD-MM-YYYY'),
            ], $boldCenter));
            $writer->addRow(Row::fromValues([]));

            // $options->mergeCells()

            $options->mergeCells(0, 5, 0, 6);
            $options->mergeCells(1, 5, 1, 6);
            $options->mergeCells(2, 5, 2, 6);
            $options->mergeCells(6, 5, 6, 6);
            $options->mergeCells(7, 5, 7, 6);
            $options->mergeCells(8, 5, 8, 6);
            $options->mergeCells(9, 5, 9, 6);
            $options->mergeCells(3, 5, 5, 5);
            $writer->addRow(Row::fromValuesWithStyle([
                'No',
                'Tanggal',
                'Ref. Opname',
                'Penyesuaian Barang',
                null,
                null,
                'Status',
                'Diajukan Oleh',
                'Ditinjau Oleh',
                'Catatan',
            ], $headerStyle));

            $writer->addRow(Row::fromValuesWithStyle([
                null,
                null,
                null,
                'Nama Barang',
                'Penyesuaian',
                'Satuan',
                null,
                null,
                null,
                null,
            ], $headerStyle));

            StockAdjustment::with([
                'user',
                'responder',
                'details',
                'details.item',
                'details.item.unit',
            ])->whereBetween('created_at', [$start, $end])
                ->where('status', StockAdjustmentStatus::REJECTED->value)
                ->orderByDesc('created_at')
                ->chunk(100, function ($stockAdjustments) use (&$writer, &$line, &$options, $bordered) {
                    foreach ($stockAdjustments as $stockAdjustment) {
                        $detailCount = $stockAdjustment->details->count();
                        if ($detailCount > 1) {
                            $mergeRowStart = $writer->getWrittenRowCount() + 1;
                            $mergeRowEnd = $writer->getWrittenRowCount() + $detailCount;
                            $options->mergeCells(0, $mergeRowStart, 0, $mergeRowEnd);
                            $options->mergeCells(1, $mergeRowStart, 1, $mergeRowEnd);
                            $options->mergeCells(2, $mergeRowStart, 2, $mergeRowEnd);
                            $options->mergeCells(6, $mergeRowStart, 6, $mergeRowEnd);
                            $options->mergeCells(7, $mergeRowStart, 7, $mergeRowEnd);
                            $options->mergeCells(8, $mergeRowStart, 8, $mergeRowEnd);
                            $options->mergeCells(9, $mergeRowStart, 9, $mergeRowEnd);
                        }
                        $writer->addRow(Row::fromValuesWithStyle([
                            $line,
                            $stockAdjustment->created_at->isoFormat('DD-MMMM-YYYY'),
                            $stockAdjustment->stockOpname?->id ?? '-',
                            $stockAdjustment->details[0]->item->name,
                            $stockAdjustment->details[0]->old_stock - $stockAdjustment->details[0]->new_stock,
                            $stockAdjustment->details[0]->old_stock,
                            $stockAdjustment->details[0]->new_stock,
                            $stockAdjustment->details[0]->item->unit->name,
                            $stockAdjustment->status->value,
                            $stockAdjustment->user->name,
                            $stockAdjustment->notes ?? '-',
                        ], $bordered));
                        for ($i = 1; $i < $stockAdjustment->details->count(); $i++) {
                            $detail = $stockAdjustment->details->get($i);
                            $writer->addRow(Row::fromValuesWithStyle([
                                null,
                                null,
                                null,
                                $detail->item->name,
                                $detail->old_stock - $detail->new_stock,
                                $detail->old_stock,
                                $detail->new_stock,
                                $detail->item->unit->name,
                                null,
                                null,
                                null,
                            ], $bordered));
                        }
                        $line++;
                    }
                });
            $writer->close();
        };

        $startString = $start->format('d-m-Y');
        $endString = $end->format('d-m-Y');
        $filename = "laporan-penyesuaian-stok.$startString-$endString.xlsx";

        return response()->streamDownload($callback, $filename);
    }
}
