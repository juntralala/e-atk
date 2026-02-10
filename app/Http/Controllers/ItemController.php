<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemRequestDetail;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderName;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\BorderWidth;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;

class ItemController extends Controller
{

    private function itemExpenditureQuery($start, $end)
    {
        return Item::with([
            'unit',
            'itemRequestDetails' => function ($q) use ($start, $end) {
                $q->whereHas('itemRequest', function ($qir) use ($start, $end) {
                    $qir->where('status', 'accepted');
                    $qir->whereBetween('response_date', [$start, $end]);
                });
            }
        ])
            ->withSum([
                'itemRequestDetails as quantity_total' => function ($q) use ($start, $end) {
                    $q->whereHas('itemRequest', function ($qir) use ($start, $end) {
                        $qir->where('status', 'accepted');
                        $qir->whereBetween('response_date', [$start, $end]);
                    });
                }
            ], 'responded_quantity')
            ->withSum([
                'itemRequestDetails as expenditure' => function ($q) use ($start, $end) {
                    $q->whereHas('itemRequest', function ($qir) use ($start, $end) {
                        $qir->where('status', 'accepted');
                        $qir->whereBetween('response_date', [$start, $end]);
                    });
                }
            ], DB::raw("responded_quantity * price"))
            ->withAvg([
                'itemRequestDetails as avg_price' => function ($q) use ($start, $end) {
                    $q->whereHas('itemRequest', function ($qir) use ($start, $end) {
                        $qir->where('status', 'accepted');
                        $qir->whereBetween('response_date', [$start, $end]);
                    });
                }
            ], 'price')
            ->orderBy('name');
    }

    public function showPage()
    {
        $items = Item::with('unit')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get();

        $units = Unit::whereNull('deleted_at')
            ->orderBy('name', 'asc')
            ->get();

        return Inertia::render('Item', [
            'items' => $items,
            'units' => $units,
        ]);
    }

    public function create(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'unit_id' => 'required|string|exists:units,id',
                'spesification_name' => 'required|string|max:255',
                'stock' => 'nullable|integer|min:0',
                'price' => 'nullable|numeric|min:0',
            ], [
                'name.required' => 'Nama barang wajib diisi',
                'name.max' => 'Nama barang maksimal 255 karakter',
                'unit_id.required' => 'Satuan wajib dipilih',
                'unit_id.exists' => 'Satuan yang dipilih tidak valid',
                'spesification_name.required' => 'Spesifikasi wajib diisi',
                'spesification_name.max' => 'Spesifikasi maksimal 255 karakter',
                'stock.integer' => 'Stok harus berupa angka',
                'stock.min' => 'Stok tidak boleh kurang dari 0',
                'price.numeric' => 'Harga harus berupa angka',
                'price.min' => 'Harga tidak boleh kurang dari 0',
            ]);

            // Check if barang with same name and spesifikasi already exists
            $exists = Item::where('name', $validated['name'])
                ->where('specification_name', $validated['spesification_name'])
                ->whereNull('deleted_at')
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'message' => 'Barang dengan nama dan spesifikasi yang sama sudah ada.',
                ]);
            }

            $item = new Item();
            $item->id = Str::uuid()->toString();
            $item->name = $validated['name'];
            $item->unit_id = $validated['unit_id'];
            $item->spesification_name = $validated['spesification_name'];
            $item->stock = $validated['stock'] ?? 0;
            $item->price = $validated['price'] ?? 0;
            $item->save();

            return redirect()->back()->with('success', 'Barang berhasil ditambahkan');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'message' => 'Terjadi kesalahan saat menyimpan barang: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Update the specified barang in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $item = Item::whereNull('deleted_at')->findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'unit_id' => 'required|string|exists:units,id',
                'spesification_name' => 'required|string|max:255',
                'stock' => 'nullable|integer|min:0',
                'price' => 'nullable|numeric|min:0',
            ], [
                'name.required' => 'Nama barang wajib diisi',
                'name.max' => 'Nama barang maksimal 255 karakter',
                'unit_id.required' => 'Satuan wajib dipilih',
                'unit_id.exists' => 'Satuan yang dipilih tidak valid',
                'spesification_name.required' => 'Spesifikasi wajib diisi',
                'spesification_name.max' => 'Spesifikasi maksimal 255 karakter',
                'stock.integer' => 'Stok harus berupa angka',
                'stock.min' => 'Stok tidak boleh kurang dari 0',
                'price.numeric' => 'Harga harus berupa angka',
                'price.min' => 'Harga tidak boleh kurang dari 0',
            ]);

            // Check if barang with same name and spesifikasi already exists (excluding current barang)
            $exists = Item::where('name', $validated['name'])
                ->where('specification_name', $validated['spesification_name'])
                ->where('id', '!=', $id)
                ->whereNull('deleted_at')
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'message' => 'Barang dengan nama dan spesifikasi yang sama sudah ada.',
                ]);
            }

            $item->name = $validated['name'];
            $item->unit_id = $validated['unit_id'];
            $item->spesification_name = $validated['spesification_name'];
            $item->stock = $validated['stock'] ?? 0;
            $item->price = $validated['price'] ?? 0;
            $item->save();

            return redirect()->back()->with('success', 'Barang berhasil diperbarui');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'message' => 'Terjadi kesalahan saat memperbarui barang: ' . $e->getMessage(),
            ]);
        }
    }

    public function delete(string $id)
    {
        try {
            $item = Item::findOrFail($id);

            // Check if barang is being used in any transactions (if you have transaction tables)
            // Uncomment and modify based on your relations
            // if ($item->transaksiMasuk()->exists() || $item->transaksiKeluar()->exists()) {
            //     throw ValidationException::withMessages([
            //         'message' => 'Barang tidak dapat dihapus karena sudah digunakan dalam transaksi.',
            //     ]);
            // }

            $item->delete();
            return redirect()->back()->with('success', 'Barang berhasil dihapus');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'message' => 'Terjadi kesalahan saat menghapus barang: ' . $e->getMessage(),
            ]);
        }
    }

    public function toXlsx()
    {
        $callback = function () {
            $writer = new Writer();
            $writer->openToFile('php://output');
            $sheet = $writer->getCurrentSheet();
            $sheet->setName('Laporan-Barang');
            $sheet->setColumnWidth(5, 1);
            $sheet->setColumnWidth(25, 2);
            $sheet->setColumnWidth(30, 3);
            $sheet->setColumnWidth(15, 4);
            $sheet->setColumnWidth(10, 5);
            $sheet->setColumnWidth(15, 6);
            $sheet->setColumnWidth(20, 7);

            $border = new Border(
                new BorderPart(BorderName::TOP, '000000', BorderWidth::THIN),
                new BorderPart(BorderName::BOTTOM, '000000', BorderWidth::THIN),
                new BorderPart(BorderName::LEFT, '000000', BorderWidth::THIN),
                new BorderPart(BorderName::RIGHT, '000000', BorderWidth::THIN),
            );

            $style = (new Style())
                ->withFontBold(true)
                ->withBackgroundColor('00B054')
                ->withCellAlignment(CellAlignment::CENTER)
                ->withBorder($border);
            $cellStyle = (new Style())
                ->withBorder($border);
            $rpStyle = (new Style())
                ->withFormat('"Rp " #,##0')
                ->withBorder($border);

            $writer->addRow(Row::fromValuesWithStyle(
                [
                    'No',
                    'Nama Barang',
                    'Nama Spesifikasi',
                    'Harga Satuan',
                    'Stok',
                    'Ukuran Satuan',
                    'Terakhir Diperbarui'
                ],
                $style
            ));

            $line = 1;
            Item::with('unit')
                ->orderBy('name')
                ->chunk(100, function ($items) use (&$line, $writer, $cellStyle, $rpStyle) {
                    foreach ($items as $item) {
                        $item->updated_at->timezone('+8');
                        $writer->addRow(Row::fromValuesWithStyles([
                            $line++,
                            $item->name,
                            $item->spesification_name,
                            (float) $item->price,
                            $item->stock,
                            $item->unit->name,
                            $item->updated_at->format('d-m-Y'),
                        ], [
                            $cellStyle,
                            $cellStyle,
                            $cellStyle,
                            $rpStyle,
                            $cellStyle,
                            $cellStyle,
                            $cellStyle
                        ]));
                    }
                });
            $writer->close();
        };
        return response()
            ->streamDownload(
                $callback,
                'laporan-stok-' . now()->format('d-m-Y') . '.xlsx'
            );
    }

    public function reportPage(Request $request)
    {
        $perPage = $request->integer('per_page' . 100);
        $page = $request->integer('page', 1);
        return inertia('ItemReport', [
            'items' => Item::with('unit')
                ->orderBy('name')
                ->paginate(perPage: $perPage, page: $page)
        ]);
    }

    public function itemExpenditureReport(Request $request)
    {
        $start = $request->date('start') ?? now()->subMonth();
        $end = $request->date('end') ?? now();
        $perPage = $request->integer('per_page' . 100);
        $page = $request->integer('page', 1);

        $start->timezone('+8')->startOfDay();
        $end->timezone('+8')->endOfDay();

        $itemExpenditures = $this->itemExpenditureQuery($start, $end)
            ->paginate(perPage: $perPage, page: $page)
            ->withQueryString();
        $total = ItemRequestDetail::whereHas('itemRequest', fn($q) => $q->whereBetween('created_at', [$start, $end]))
            ->sum(DB::raw('responded_quantity * price'));
        return inertia('ItemExpenditureReport', [
            'itemExpenditures' => $itemExpenditures,
            'total' => (float) $total
        ]);
    }

    public function toExpenditureXlsx(Request $request)
    {
        $start = $request->date('start') ?? now()->subMonth();
        $end = $request->date('end') ?? now();

        $start->timezone('+8')->startOfDay();
        $end->timezone('+8')->endOfDay();

        $callback = function () use ($start, $end) {
            $writer = new Writer();
            $writer->openToFile('php://output');
            $writer->getCurrentSheet()->setName('Laporan-Pengeluaran-Barang');
            $sheet = $writer->getCurrentSheet();
            $sheet->setColumnWidth(25, 1);
            $sheet->setColumnWidth(30, 2);
            $sheet->setColumnWidth(15, 3);
            $sheet->setColumnWidth(15, 4);
            $sheet->setColumnWidth(15, 5);
            $sheet->setColumnWidth(15, 6);
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
                    'Nama Barang',
                    'Nama Spesifikasi',
                    'Jumlah',
                    'Ukuran Satuan',
                    'Harga Satuan',
                    'Pengeluaran',
                ],
                $headerStyle
            ));

            // Variable untuk menyimpan total
            $grandTotal = 0;

            $this->itemExpenditureQuery($start, $end)
                ->chunk(100, function ($items) use ($writer, $cellStyle, $rpStyle, &$grandTotal) {
                    foreach ($items as $item) {
                        // Akumulasi total
                        $grandTotal += $item->expenditure ?? 0;

                        $writer->addRow(Row::fromValuesWithStyles([
                            $item->name,
                            $item->spesification_name,
                            (int) $item->quantity_total ?? 0,
                            $item->unit->name,
                            (float) $item->avg_price ?? 0,
                            (float) $item->expenditure ?? 0,
                        ], [
                            $cellStyle,
                            $cellStyle,
                            $cellStyle,
                            $cellStyle,
                            $rpStyle,
                            $rpStyle,
                        ]));
                    }
                });

            // Write total row
            $writer->addRow(Row::fromValuesWithStyles([
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
                $totalRpStyle,
            ]));
            $writer->close();
        };

        $format = 'd-m-Y';
        $start->timezone('+8');
        $end->timezone('+8');
        return response()
            ->streamDownload(
                $callback,
                'laporan-pengeluaran-barang-' . $start->format($format) . '-' . $end->format($format) . '.xlsx'
            );
    }
}