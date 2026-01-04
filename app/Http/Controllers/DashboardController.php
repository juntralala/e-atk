<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Sku;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Recipient;
use App\Service\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $service
    ) {
    }

    public function index(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $totalItems = Item::count();
        $totalSkus = Sku::count();
        $lowStockSkus = Sku::where('quantity', '<=', 10)->count();
        $totalRecipients = Recipient::count();
        $transactionSummary = Transaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->selectRaw('
                type,
                COUNT(*) as total_transactions,
                SUM(
                    (SELECT SUM(ti.base_quantity * IF(ti.price, ti.price, 1))
                     FROM transaction_items ti
                     WHERE ti.transaction_id = transactions.id)
                ) as total_value
            ')
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        $inboundCount = $transactionSummary->get('in')?->total_transactions ?? 0;
        $outboundCount = $transactionSummary->get('out')?->total_transactions ?? 0;
        $inboundValue = $transactionSummary->get('in')?->total_value ?? 0;
        $outboundValue = $transactionSummary->get('out')?->total_value ?? 0;

        $recentTransactions = Transaction::with(['user:id,name', 'recipient:id,name'])
            ->withSum('transactionItems as total_value', DB::raw('base_quantity * price'))
            ->latest('transaction_date')
            ->take(10)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'transaction_date' => $transaction->transaction_date,
                    'recipient' => $transaction->recipient?->name,
                    'supplier' => $transaction->supplier,
                    'total_value' => (int) (($transaction->type == 'in') ? +$transaction->total_value : -$transaction->total_value),
                    'created_by' => $transaction->user?->name,
                ];
            });

        $lowStockItems = Sku::with(['item:id,name', 'item.baseMeasurementUnit:id,name'])
            ->where('quantity', '<=', 10)
            ->orderBy('quantity', 'asc')
            ->take(10)
            ->get()
            ->map(function ($sku) {
                return [
                    'id' => $sku->id,
                    'sku' => $sku->sku,
                    'item_name' => $sku->item?->name,
                    'specification' => $sku->specification_name,
                    'quantity' => $sku->quantity,
                    'unit' => $sku->item?->baseMeasurementUnit?->name,
                ];
            });

        $topItems = TransactionItem::join('skus', 'transaction_items.sku_id', '=', 'skus.id')
            ->join('items', 'skus.item_id', '=', 'items.id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->whereBetween('transactions.transaction_date', [$startDate, $endDate])
            ->select(
                'items.id',
                'items.name',
                DB::raw('SUM(transaction_items.quantity) as total_quantity'),
                DB::raw('COUNT(DISTINCT transaction_items.transaction_id) as transaction_count')
            )
            ->groupBy('items.id', 'items.name')
            ->orderByDesc('total_quantity')
            ->take(5)
            ->get();

        $monthlyTrend = Transaction::whereBetween('transaction_date', [now()->subMonths(6), now()])
            ->selectRaw('
                DATE_FORMAT(transaction_date, "%Y-%m") as month,
                type,
                COUNT(*) as count
            ')
            ->groupBy('month', 'type')
            ->orderBy('month')
            ->get()
            ->groupBy('month')
            ->map(function ($group) {
                return [
                    'inbound' => $group->where('type', 'in')->first()?->count ?? 0,
                    'outbound' => $group->where('type', 'out')->first()?->count ?? 0,
                ];
            });

        $topRecipients = Transaction::join('recipients', 'transactions.recipient_id', '=', 'recipients.id')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->where('type', 'out')
            ->select(
                'recipients.id',
                'recipients.name',
                DB::raw('COUNT(*) as transaction_count')
            )
            ->groupBy('recipients.id', 'recipients.name')
            ->orderByDesc('transaction_count')
            ->take(5)
            ->get();

        $inventoryValue = Sku::sum(DB::raw('quantity * price'));

        return Inertia::render('Home', [
            'statistics' => [
                'total_items' => $totalItems,
                'total_skus' => $totalSkus,
                'low_stock_count' => $lowStockSkus,
                'total_recipients' => $totalRecipients,
                'inbound_count' => $inboundCount,
                'outbound_count' => $outboundCount,
                'inbound_value' => $inboundValue,
                'outbound_value' => $outboundValue,
                'inventory_value' => $inventoryValue,
            ],
            'recent_transactions' => $recentTransactions,
            'low_stock_items' => $lowStockItems,
            'top_items' => $topItems,
            'monthly_trend' => $monthlyTrend,
            'top_recipients' => $topRecipients,
            'date_range' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    public function getExpendituresPerSKU(Request $request)
    {
        $start = $request->date('start');
        $end = $request->date('end');
        $page = $request->input('page', 1);
        $search = $request->input('search');
        return response()->json(
            $this->service->getExpendituresPerSKU($search, $start, $end, $page)
        );
    }

    public function toXlsx(Request $request)
    {
        $start = $request->date('start');
        $end = $request->date('end');
        $service = $this->service;
        $callback = function () use ($service, $start, $end) {
            try {
                $writer = new Writer();
                $writer->openToFile('php://output');
                $writer->addRow(Row::fromValues(['Nama Barang', 'SKU', 'Nama Spesifikasi', 'Jumlah', 'Satuan', 'Harga/Satuan', 'Total Pengeluaran']));
                $service->exportXlsx(function ($skus) use ($writer) {
                    foreach ($skus as $sku) {
                        $writer->addRow(Row::fromValues([
                            $sku->item->name,
                            $sku->sku,
                            $sku->specification_name,
                            $sku->total_quantity,
                            $sku->item->baseMeasurementUnit->name,
                            (float) $sku->out_price,
                            (float) $sku->expenditure,
                        ]));
                    }
                }, $start, $end);
            } finally {
                $writer->close();
            }
        };
        return response()->streamDownload($callback, $this->service->makeXlsxExportFileName('pengeluaran_per_sku', $start, $end));
    }

    public function getExpendituresPerItem(Request $request)
    {
        $search = $request->string('search');
        $start = $request->date('start');
        $end = $request->date('end');
        $page = $request->integer('page', 1);
        return response()->json(
            $this->service->getExpendituresPerItem($search, $start, $end, $page)
        );
    }

    public function toXlsxExpendituresPerItem(Request $request)
    {
        $start = $request->date('start');
        $end = $request->date('end');
        $service = $this->service;
        $callback = function () use ($service, $start, $end) {
            try {
                $writer = new Writer();
                $writer->openToFile('php://output');
                $writer->addRow(Row::fromValues(['Nama Barang', 'Jumlah', 'Satuan', 'Harga/Satuan', 'Total Pengeluaran']));
                $service->toXlsxExpendituresPerItem(function ($items) use ($writer) {
                    foreach ($items as $item) {
                        $writer->addRow(Row::fromValues([
                            $item->name,
                            $item->quantity_total,
                            $item->baseMeasurementUnit->name,
                            (float) $item->out_price ?? $item->skus->avg('price') ?? 0,
                            (float) $item->expenditure,
                        ]));
                    }
                }, $start, $end);
            } finally {
                $writer->close();
            }
        };
        return response()->streamDownload($callback, $this->service->makeXlsxExportFileName('pengeluaran_per_item', $start, $end));
    }
}