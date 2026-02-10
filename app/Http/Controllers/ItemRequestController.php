<?php

namespace App\Http\Controllers;

use App\Events\ItemRequestAccepted;
use App\Events\ItemRequestCreated;
use App\Events\ItemRequestRejected;
use App\Http\Requests\ItemRequestCreateRequest;
use App\Models\Item;
use App\Models\ItemRequest;
use App\Models\ItemRequestDetail;
use App\Models\User;
use Exception;
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
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Throwable;

class ItemRequestController extends Controller
{    

    private function itemRequestReportQuery($start, $end, $status)
    {
        return ItemRequest::with('requester')
            ->with('responder')
            ->with('itemRequestDetails')
            ->with('itemRequestDetails.item')
            ->with('itemRequestDetails.item.unit')
            ->when(!blank($status), fn($q) => $q->where('status', $status))
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at', 'desc');
    }

    private function unitExpenditureQuery($start, $end)
    {
        return User::whereHas('role', fn($q) => $q->where('name', 'unit'))
            ->with([
                'itemRequests' => function ($q) use ($start, $end) {
                    $q->where('status', 'accepted')
                        ->whereBetween('response_date', [$start, $end])
                        ->with('itemRequestDetails');
                }
            ])
            ->orderBy('name');
    }

    public function showItemRequestForm()
    {
        return Inertia::render('ItemRequestForm', [
            'items' => Item::with('unit')->get()
        ]);
    }

    public function showPage(Request $request)
    {
        $page = $request->integer('page', 1);
        $perPage = $request->integer('per_page', 10);
        $search = $request->input('search');
        $status = $request->input('status');

        $itemRequestPage = ItemRequest::query()
            ->with('requester')
            ->with('responder')
            ->with('itemRequestDetails')
            ->with('itemRequestDetails.item')
            ->with('itemRequestDetails.item.unit')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('requester', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    })
                        ->orWhereHas('responder', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        })
                        ->orWhere('responder_notes', 'like', "%{$search}%");
                });
            })
            // filter status
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            // hanya mengambil permintaan user yang login saat ini jika role adalah ruangan
            ->when($request->user()->role->name == 'unit', function ($q) {
                $q->where('requester_id', request()->user()->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(perPage: $perPage, page: $page)
            ->withQueryString(); // Penting untuk mempertahankan query string saat pagination

        return Inertia::render('ItemRequest', [
            'itemRequests' => $itemRequestPage,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function create(ItemRequestCreateRequest $request)
    {
        try {
            DB::beginTransaction();
            $itemRequest = ItemRequest::create([
                'requester_id' => $request->user()->id,
                'status' => 'pending',
                'request_date' => Date::now(),
            ]);
            foreach ($request->items as $item) {
                $itemData = Item::findOrFail($item['item_id']);

                if($itemData->stock < $item['requested_quantity']) {
                    throw new Exception("Stok $itemData->name tidak mencukupi, tersedia: $itemData->stock");
                }

                ItemRequestDetail::create([
                    'item_request_id' => $itemRequest->id,
                    'item_id' => $item['item_id'],
                    'requested_quantity' => $item['requested_quantity'],
                    'responded_quantity' => 0,
                    'price' => $itemData->price,
                ]);
            }

            DB::commit();
            ItemRequestCreated::dispatch($itemRequest);

            return redirect()
                ->route('items.requests')
                ->with('success', 'Permintaan barang berhasil dibuat.');

        } catch (Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Gagal membuat permintaan barang: ' . $e->getMessage()]);
        }
    }

    public function delete(ItemRequest $itemRequest, Request $request)
    {
        if (!$itemRequest) {
            return back()->withErrors([
                'message' => 'Permintaan barang yang ingin dihapus tidak ditemukan',
            ]);
        }
        if ($itemRequest->status == 'accepted') {
            return back()->withErrors([
                'message' => 'Permintaan barang sudah tidak bisa dihapus lagi karna telah berstatus terima',
            ]);
        }
        if ($itemRequest->status == 'rejected') {
            return back()->withErrors([
                'message' => 'Permintaan barang sudah tidak bisa dihapus lagi karna telah berstatus ditolak',
            ]);
        }
        $itemRequest->delete();
        return back();
    }

    public function reject(?ItemRequest $itemRequest, Request $request)
    {
        if ($itemRequest == null) {
            return back()->with(['error' => 'Permintaan barang yang ingin ditolak tidak ditemukan atau dihapus']);
        }

        $validated = $request->validate([
            'responder_notes' => 'required|string|min:1'
        ]);

        $itemRequest->status = 'rejected';
        $itemRequest->responder_notes = $validated['responder_notes'];
        $itemRequest->responder_id = auth()->id();
        $itemRequest->response_date = now();
        $itemRequest->responded_at = now();
        $itemRequest->save();

        ItemRequestRejected::dispatch($itemRequest);
        return back()->with(['message' => 'Permintaan barang berhasil ditolak']);
    }

    public function accept(?ItemRequest $itemRequest, Request $request)
    {
        if ($itemRequest == null) {
            return back()->with(['error' => 'Permintaan barang yang ingin diterima tidak ditemukan atau dihapus']);
        }

        $validated = $request->validate([
            'responder_notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:item_request_details,id',
            'items.*.received_quantity' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();
            foreach ($validated['items'] as $item) {
                $itemDetail = $itemRequest->itemRequestDetails()->find($item['id']);
                if (!$itemDetail) {
                    throw new \Exception('Item detail tidak ditemukan');
                }
                // Validasi apakah received_quantity tidak melebihi requested_quantity
                // if ($item['received_quantity'] > $itemDetail->requested_quantity) {
                //     throw new \Exception('Jumlah yang diterima untuk ' . $itemDetail->item->name . ' melebihi jumlah yang diminta');
                // }
                // Validasi apakah stok mencukupi
                if ($item['received_quantity'] > $itemDetail->item->stock) {
                    throw new \Exception('Stok ' . $itemDetail->item->name . ' tidak mencukupi. Stok tersedia: ' . $itemDetail->item->stock);
                }
            }

            // Update status item request menjadi accepted
            $itemRequest->status = 'accepted';
            $itemRequest->responder_notes = $validated['responder_notes'];
            $itemRequest->responder_id = auth()->id();
            $itemRequest->response_date = now();
            $itemRequest->responded_at = now();
            $itemRequest->save();

            // Update received_quantity untuk setiap item detail
            foreach ($validated['items'] as $item) {
                $itemDetail = $itemRequest->itemRequestDetails()->find($item['id']);

                if ($itemDetail) {
                    $itemDetail->responded_quantity = $item['received_quantity'];
                    $itemDetail->price = $itemDetail->item->price;
                    $itemDetail->save();
                }
                $itemDetail->item->decrement('stock', $itemDetail->responded_quantity);
            }
            DB::commit();
            ItemRequestAccepted::dispatch($itemRequest);
            return back()->with(['success' => 'Permintaan barang berhasil diterima']);
        } catch (Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function reportPage(Request $request)
    {
        $page = $request->integer('page');
        $perPage = $request->integer('per_page');

        $start = $request->date('start') ?? now()->subMonth();
        $end = $request->date('end') ?? now();
        $status = $request->input('status');

        $start->timezone('+8')->startOfDay();
        $end->timezone('+8')->endOfDay();

        $itemRequests = $this->itemRequestReportQuery($start, $end, $status)
            ->paginate($perPage, page: $page)
            ->withQueryString();
        $total = (!empty($status) && $status != 'accepted') ? 0 : ItemRequestDetail::whereHas('itemRequest', function ($q) use ($start, $end) {
            $q->whereBetween('created_at', [$start, $end]);
            $q->where('status', 'accepted');
        })->sum(DB::raw('price * responded_quantity'));
        return inertia('ItemRequestReport', [
            'itemRequests' => $itemRequests,
            'total' => (float) $total
        ]);
    }

    public function toXlsx(Request $request)
    {
        $start = $request->date('start') ?? now()->subMonth();
        $end = $request->date('end') ?? now();
        $status = $request->input('status');

        $start->timezone('+8')->startOfDay();
        $end->timezone('+8')->endOfDay();

        $callback = function () use ($start, $end, $status) {
            $writer = new Writer();
            $writer->openToFile('php://output');
            $sheet = $writer->getCurrentSheet();
            $sheet->setName('Laporan-Permintaan-Barang');
            $sheet->setColumnWidth(5, 1);  // No
            $sheet->setColumnWidth(15, 2);  // Tanggal Permintaan
            $sheet->setColumnWidth(20, 3);  // Pembuat Permintaan
            $sheet->setColumnWidth(25, 4);  // Nama Barang
            $sheet->setColumnWidth(25, 5);  // Nama Spesifikasi
            $sheet->setColumnWidth(15, 6);  // Jumlah Diminta
            $sheet->setColumnWidth(15, 7);  // Jumlah Diterima
            $sheet->setColumnWidth(12, 8);  // Satuan
            $sheet->setColumnWidth(15, 9);  // Harga Satuan
            $sheet->setColumnWidth(15, 10);  // Status
            $sheet->setColumnWidth(20, 11);  // Petugas
            $sheet->setColumnWidth(15, 12); // Tanggal Respon
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
            $numberStyle = (new Style())
                ->withFormat('#,##0')
                ->withBorder($border);
            $rpStyle = (new Style())
                ->withFormat('"Rp " #,##0')
                ->withBorder($border);
            $writer->addRow(Row::fromValuesWithStyle([
                'No',
                'Tanggal Permintaan',
                'Pembuat Permintaan',
                'Nama Barang',
                'Nama Spesifikasi',
                'Jumlah Diminta',
                'Jumlah Diterima',
                'Satuan',
                'Harga Satuan',
                'Status',
                'Petugas',
                'Tanggal Respon',
            ], $headerStyle));

            $line = 1;
            $this->itemRequestReportQuery($start, $end, $status)
                ->chunk(100, function ($itemRequests) use (&$line, $writer, $cellStyle, $numberStyle, $rpStyle) {
                    foreach ($itemRequests as $itemRequest) {
                        $itemRequest->created_at->timezone('+8');
                        if ($itemRequest->responded_at != null) {
                            $itemRequest->responded_at->timezone('+8');
                        }
                        foreach ($itemRequest->itemRequestDetails as $detail) {
                            $writer->addRow(Row::fromValuesWithStyles([
                                $line++,
                                $itemRequest->created_at->format('d-m-Y'),
                                $itemRequest->requester->name,
                                $detail->item->name,
                                $detail->item->spesification_name,
                                (float) $detail->requested_quantity,
                                (float) $detail->responded_quantity,
                                $detail->item->unit->name,
                                (float) $detail->price,
                                __("status.$itemRequest->status"),
                                $itemRequest->responder->name ?? '-',
                                $itemRequest->responded_at?->format('d-m-Y') ?? '-',
                            ], [
                                $cellStyle,      // No
                                $cellStyle,      // Tanggal Permintaan
                                $cellStyle,      // Pembuat Permintaan
                                $cellStyle,      // Nama Barang
                                $cellStyle,      // Nama Spesifikasi
                                $numberStyle,    // Jumlah Diminta
                                $numberStyle,    // Jumlah Diterima
                                $cellStyle,      // Satuan
                                $rpStyle,        // Harga Satuan
                                $cellStyle,      // Status
                                $cellStyle,      // Petugas
                                $cellStyle,      // Tanggal Respon
                            ]));
                        }
                    }
                });
            $writer->close();
        };

        $start->timezone('+8');
        $end->timezone('+8');
        $format = 'd-m-Y';
        $filename = 'laporan-permintaan-barang-' . $start->format($format) . '-' . $end->format($format) . '.xlsx';
        return response()->streamDownload($callback, $filename);
    }

    public function unitExpenditureReport(Request $request)
    {
        $start = $request->date('start') ?? now()->subMonth();
        $end = $request->date('end') ?? now();

        $start->timezone('+8')->startOfDay();
        $end->timezone('+8')->endOfDay();

        $page = $request->integer('page');
        $perPage = $request->integer('per_page');

        $paginator = $this->unitExpenditureQuery($start, $end)
            ->paginate(perPage: $perPage, page: $page)
            ->through(function ($item) {
                $item->expenditure = $item->itemRequests->sum(function ($request) {
                    return $request->itemRequestDetails->sum(function ($detail) {
                        return $detail->responded_quantity * $detail->price;
                    });
                });

                return $item;
            });
        $totalExpenditure = ItemRequestDetail::whereHas('itemRequest', fn($q) => $q->whereBetween('created_at', [$start, $end]))
            ->sum(DB::raw('responded_quantity * price'));
        return inertia('UnitExpenditureReport', [
            'unitExpenditures' => $paginator,
            'total' => (float) $totalExpenditure
        ]);
    }

    public function toUnitExpenditureXlsx(Request $request)
    {
        $start = $request->date('start') ?? now()->subMonth();
        $end = $request->date('end') ?? now();

        $start->timezone('+8')->startOfDay();
        $end->timezone('+8')->endOfDay();

        $callback = function () use ($start, $end) {
            $writer = new Writer();
            $writer->openToFile('php://output');
            $sheet = $writer->getCurrentSheet();
            $sheet->setName('Laporan-Permintaan-Barang');
            $sheet->setColumnWidth(25, 1);
            $sheet->setColumnWidth(15, 2);

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
                    'Nama Unit',
                    'Pengeluaran'
                ],
                $headerStyle
            ));

            $grandTotal = 0;

            $this->unitExpenditureQuery($start, $end)
                ->chunk(100, function ($items) use ($writer, $cellStyle, $rpStyle, &$grandTotal) {
                    foreach ($items as $item) {
                        // Hitung manual expenditure
                        $expenditure = $item->itemRequests->sum(function ($request) {
                            return $request->itemRequestDetails->sum(function ($detail) {
                                return $detail->responded_quantity * $detail->price;
                            });
                        });

                        $grandTotal += $expenditure;

                        $writer->addRow(Row::fromValuesWithStyles([
                            $item->name,
                            (float) $expenditure,
                        ], [
                            $cellStyle,
                            $rpStyle,
                        ]));
                    }
                });

            $writer->addRow(Row::fromValuesWithStyles([
                'Total',
                (float) $grandTotal,
            ], [
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
                'laporan-pengeluaran-unit-' . $start->format($format) . '-' . $end->format($format) . '.xlsx'
            );
    }
}
