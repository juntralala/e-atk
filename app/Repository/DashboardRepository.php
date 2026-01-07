<?php
namespace App\Repository;

use App\Dto\Response\ExpenditurePerItem;
use App\Dto\Response\ExpenditurePerSKU;
use App\Dto\Response\PaginatedResponse;
use App\Models\Item;
use App\Models\Sku;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DashboardRepository
{

    // tempat membuat query builder, terminate method di tempat lain
    private function expenditurePerSKUQuery($search, $start, $end)
    {
        return Sku::with([
            'item.baseMeasurementUnit:id,name',
            'transactionItems' => function ($query) use ($start, $end) {
                $query->whereHas('transaction', function ($tq) use ($start, $end) {
                    $tq->where('type', 'out');
                    $tq->whereBetween('transaction_date', [$start, $end]);
                });
            }
            ])
            ->when($search, function ($query) use ($search) {
                $query->orWhereLike('sku', "%$search%");
                $query->orWhereLike('spesification_name', "%$search%");
                $query->orWhereHas('item', fn($iq) => $iq->whereLike('name', "%$search%"));
            })
            ->withSum([
                'transactionItems as total_quantity' => function ($q) use ($start, $end) {
                    $q->whereHas('transaction', function ($tq) use ($start, $end) {
                        $tq->where('type', 'out');
                        $tq->whereBetween('transaction_date', [$start, $end]);
                    });
                }
            ], 'base_quantity')
            ->withSum([
                'transactionItems as expenditure' => function ($q) use ($start, $end) {
                    $q->whereHas('transaction', function ($tq) use ($start, $end) {
                        $tq->where('type', 'out');
                        $tq->whereBetween('transaction_date', [$start, $end]);
                    });
                }
            ], DB::raw("base_quantity * price"))
            ->withAvg([
                'transactionItems as out_price' => function ($q) use ($start, $end) {
                    $q->whereHas('transaction', function ($tq) use ($start, $end) {
                        $tq->where('type', 'out');
                        $tq->whereBetween('transaction_date', [$start, $end]);
                    });
                }
            ], 'price');
    }

    private function expenditurePerItemQuery($search, $start, $end) {
        return Item::with([
            'baseMeasurementUnit:id,name',
            'skus.transactionItems' => function ($q) use ($start, $end) {
                $q->whereHas('transaction', function ($tq) use ($start, $end) {
                    $tq->where('type', 'out');
                    $tq->whereBetween('transaction_date', [$start, $end]);
                });
            }
        ])
            ->select(['id', 'name', 'base_measurement_unit_id'])
            ->when($search != null, function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
            })
            ->withSum([
                'skus as expenditure' => function ($query) use ($start, $end) {
                    $query->join('transaction_items', 'skus.id', '=', 'transaction_items.sku_id')
                        ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                        ->where('transactions.type', 'out')
                        ->whereBetween('transactions.transaction_date', [$start, $end]);
                }
            ], DB::raw('transaction_items.base_quantity * transaction_items.price'))
            ->withSum([
                'skus as quantity_total' => function ($query) use ($start, $end) {
                    $query->join('transaction_items', 'skus.id', '=', 'transaction_items.sku_id')
                        ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                        ->where('transactions.type', 'out')
                        ->whereBetween('transactions.transaction_date', [$start, $end]);
                }
            ], 'transaction_items.base_quantity')
            ->withAvg([
                'skus as out_price' => function ($query) use ($start, $end) {
                    $query->join('transaction_items', 'skus.id', '=', 'transaction_items.sku_id')
                        ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                        ->where('transactions.type', 'out')
                        ->whereBetween('transactions.transaction_date', [$start, $end]);
                }
            ], 'transaction_items.price');
    }

    public function getExpendituresPerSKU($search, $start, $end, $page = 1)
    {
        if ($start == null || $end == null) {
            throw new RuntimeException('start dan end date cannot be null');
        }
        $expenditures = collect();
        $skusPage = $this->expenditurePerSKUQuery($search, $start, $end)
            ->paginate(perPage: 10, page: $page);

        foreach ($skusPage as $sku) {
            $expenditures->add(new ExpenditurePerSKU(
                $sku->item->name,
                $sku->sku,
                $sku->spesification_name,
                $sku->total_quantity ?? 0,
                $sku->item->baseMeasurementUnit->name,
                $sku->out_price ?? $sku->price ?? 0,
                $sku->expenditure ?? 0
            ));
        }

        return new PaginatedResponse(
            $expenditures,
            $skusPage->currentPage(),
            $skusPage->perPage(),
            $skusPage->lastPage(),
            $skusPage->total(),
        );
    }

    public function exportXlsx($callback, $start, $end)
    {
        return $this->expenditurePerSKUQuery(null, $start, $end)
            ->chunk(100, $callback);
    }

    public function getExpenditurePerItem($search = null, $start = null, $end = null, $page = 1)
    {
        $expenditures = collect();
        $itemsPage = $this->expenditurePerItemQuery($search, $start, $end)
            ->paginate(perPage: 10, page: $page);
        foreach ($itemsPage as $item) {
            $expenditures->add(new ExpenditurePerItem(
                $item->name,
                $item->quantity_total ?? 0,
                $item->baseMeasurementUnit->name,
                $item->out_price ?? $item->skus->avg('price') ?? 0,
                $item->expenditure ?? 0
            ));
        }

        return new PaginatedResponse(
            data: $expenditures,
            currentPage: $itemsPage->currentPage(),
            perPage: $itemsPage->perPage(),
            lastPage: $itemsPage->lastPage(),
            total: $itemsPage->total()
        );
    }

    public function exportXlsxExpendituresPerItem(callable $callable, $start, $end) {
        return $this->expenditurePerItemQuery(null, $start, $end)
            ->chunk(100, $callable);
    }
}