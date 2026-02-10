<?php
namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemAddition;
use App\Models\ItemAdditionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderName;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\BorderWidth;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;

class ItemAdditionController extends Controller
{

    public function showPage()
    {
        return Inertia::render('ItemAddition', [
            'items' => Item::with('unit')->get()
        ]);
    }

    public function create(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'addition_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ], [
            'addition_date.required' => 'Tanggal harus diisi',
            'items.required' => 'Minimal harus ada 1 barang',
            'items.*.item_id.required' => 'Barang harus dipilih',
            'items.*.item_id.exists' => 'Barang tidak ditemukan',
            'items.*.quantity.required' => 'Jumlah harus diisi',
            'items.*.quantity.min' => 'Jumlah minimal 1',
            'items.*.price.required' => 'Harga harus diisi',
            'items.*.price.min' => 'Harga tidak boleh negatif',
        ]);

        try {
            DB::beginTransaction();

            $penambahan = ItemAddition::create([
                'user_id' => $request->user()->id,
                'addition_date' => $validated['addition_date'],
            ]);

            foreach ($validated['items'] as $item) {
                ItemAdditionDetail::create([
                    'item_addition_id' => $penambahan->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);

                $barang = Item::findOrFail($item['item_id']);
                if ($barang) {
                    $barang->increment('stock', $item['quantity']);
                }
            }
            DB::commit();
            return redirect()->back()->with('success', 'Penambahan barang berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function toXlsx(Request $request)
    {
        $start = $request->date('start') ?? now()->subMonth();
        $end = $request->date('end') ?? now();

        $start->timezone('+8')->startOfDay();
        $end->timezone('+8')->endOfDay();

        $callback = function () use ($start, $end) {
            $writer = new Writer();
            $writer->openToFile('php://output');
            $sheet = $writer->getCurrentSheet();
            $sheet->setName('Laporan-Penambahan-Barang');
            $sheet->setColumnWidth(5, 1);
            $sheet->setColumnWidth(25, 2);
            $sheet->setColumnWidth(35, 3);
            $sheet->setColumnWidth(20, 4);
            $sheet->setColumnWidth(10, 5);
            $sheet->setColumnWidth(10, 6);
            $sheet->setColumnWidth(15, 7);
            $sheet->setColumnWidth(15, 8);
            $sheet->setColumnWidth(15, 9);
            $border = new Border(
                new BorderPart(BorderName::TOP, '000000', BorderWidth::THIN),
                new BorderPart(BorderName::BOTTOM, '000000', BorderWidth::THIN),
                new BorderPart(BorderName::LEFT, '000000', BorderWidth::THIN),
                new BorderPart(BorderName::RIGHT, '000000', BorderWidth::THIN),
            );

            $headerStyle = (new Style())
                ->withFontBold(true)
                ->withBackgroundColor('00B054')
                ->withCellAlignment(CellAlignment::CENTER)
                ->withBorder($border);
            $cellStyle = (new Style())
                ->withBorder($border);
            $rpStyle = (new Style())
                ->withFormat('"Rp " #,##0')
                ->withBorder($border);
            $totalStyle = (new Style())
                ->withFontBold(true)
                ->withBackgroundColor('FFD966')
                ->withCellAlignment(CellAlignment::RIGHT)
                ->withBorder($border);
            $totalRpStyle = (new Style())
                ->withFontBold(true)
                ->withBackgroundColor('FFD966')
                ->withFormat('"Rp " #,##0')
                ->withBorder($border);

            $writer->addRow(Row::fromValuesWithStyle(
                [
                    'No',
                    'Nama Barang',
                    'Nama Spesifikasi',
                    'Petugas',
                    'Tanggal',
                    'Jumlah',
                    'Ukuran Satuan',
                    'Harga Satuan',
                    'Subtotal'
                ],
                $headerStyle
            ));

            // Variable untuk menyimpan total
            $grandTotal = 0;
            $line = 1;
            ItemAddition::with([
                'user',
                'itemAdditionDetails',
                'itemAdditionDetails.item',
                'itemAdditionDetails.item.unit',
            ])
                ->whereBetween('addition_date', [$start, $end])
                ->chunk(100, function ($itemAdditions) use ($writer, $cellStyle, $rpStyle, &$grandTotal, &$line) {
                    foreach ($itemAdditions as $itemAddition) {
                        foreach ($itemAddition->itemAdditionDetails as $detail) {
                            $subtotal = $detail->price * $detail->quantity;
                            $grandTotal += $subtotal;

                            $writer->addRow(Row::fromValuesWithStyles([
                                $line++,
                                $detail->item->name,
                                $detail->item->specification_name,
                                $itemAddition->user->name,
                                $itemAddition->addition_date->format('d-m-Y'),
                                $detail->quantity,
                                $detail->item->unit->name,
                                (float) $detail->price,
                                (float) $subtotal,
                            ], [
                                $cellStyle,
                                $cellStyle,
                                $cellStyle,
                                $cellStyle,
                                $cellStyle,
                                $cellStyle,
                                $cellStyle,
                                $rpStyle,
                                $rpStyle,
                            ]));
                        }
                    }
                });

            $writer->addRow(Row::fromValuesWithStyles([
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'Total',
                (float) $grandTotal,
            ], [
                $totalStyle,
                $totalStyle,
                $totalStyle,
                $totalStyle,
                $totalStyle,
                $totalStyle,
                $totalStyle,
                $totalStyle,
                $totalRpStyle,
            ]));

            $writer->close();
        };

        $format = 'd-m-Y';
        $start->timezone("+8");
        $end->timezone("+8");
        return response()
            ->streamDownload(
                $callback,
                'laporan-penambahan-barang-' . $start->format($format) . '-' . $end->format($format) . '.xlsx'
            );
    }

    public function reportPage(Request $request)
    {
        $perPage = $request->integer('per_page');
        $page = $request->integer('page');
        $start = $request->date('start') ?? now()->subMonth();
        $end = $request->date('end') ?? now();

        $start->timezone('+8')->startOfDay();
        $end->timezone('+8')->endOfDay();

        $itemAdditions = ItemAddition::with([
            'user',
            'itemAdditionDetails',
            'itemAdditionDetails.item',
            'itemAdditionDetails.item.unit',
        ])
            ->whereBetween('addition_date', [$start, $end])
            ->paginate(perPage: $perPage, page: $page)
            ->withQueryString();
        $priceTotal = ItemAdditionDetail::query()
            ->whereHas('itemAddition', fn($q) => $q->whereBetween('addition_date', [$start, $end]))
            ->sum(DB::raw('price * quantity'));
        return inertia('ItemAdditionReport', [
            'itemAdditions' => $itemAdditions,
            'priceTotal' => (float) $priceTotal
        ]);
    }
}