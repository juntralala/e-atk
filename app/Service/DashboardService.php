<?php

namespace App\Service;

use App\Repository\DashboardRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

class DashboardService
{
    public function __construct(
        private DashboardRepository $repository
    ) {
    }

    // template nama file
    public function makeXlsxExportFileName(string $prefix, ?Carbon $start, ?Carbon $end) {
        if ($start == null) {
            $start = Date::now()->subMonth();
        }
        if ($end == null) {
            $end = Date::now();
        }
        return $prefix . '_' . $start->timezone('+8')->format('d-m-Y') . '_' . $start->timezone('+8')->format('d-m-Y') . '.xlsx';
    }

        /**
     * @return \App\Dto\Response\PaginatedResponse<\App\Dto\Response\ExpenditurePerSKU>
     */
    public function getExpendituresPerSKU(?string $search, ?Carbon $start, ?Carbon $end, int $page = 1)
    {
        if ($start == null) {
            $start = Date::now()->subMonth();
        }
        if ($end == null) {
            $end = Date::now();
        }
        return $this->repository->getExpendituresPerSKU($search, $start, $end, $page);
    }

    /**
     * @param  callable(\Illuminate\Support\Collection<int, \App\Models\Sku>, int): mixed  $callback
     */
    public function exportXlsx(callable $callback, ?Carbon $start, ?Carbon $end)
    {
        if ($start == null) {
            $start = Date::now()->subMonth();
        }
        if ($end == null) {
            $end = Date::now();
        }
        return $this->repository->exportXlsx($callback, $start, $end);
    }

    /**
     * @return \App\Dto\Response\PaginatedResponse<\App\Dto\Response\ExpenditurePerItem>
     */
    public function getExpendituresPerItem(?string $search, ?Carbon $start, ?Carbon $end, int $page = 1)
    {
        if ($start == null) {
            $start = Date::now()->subMonth();
        }
        if ($end == null) {
            $end = Date::now();
        }
        return $this->repository->getExpenditurePerItem($search, $start, $end, $page);
    }

    /**
     * @param  callable(\Illuminate\Support\Collection<int, \App\Models\Item>, int): mixed  $callback
     */
    public function toXlsxExpendituresPerItem(callable $callback, ?Carbon $start, ?Carbon $end) {
        if ($start == null) {
            $start = Date::now()->subMonth();
        }
        if ($end == null) {
            $end = Date::now();
        }
        return $this->repository->exportXlsxExpendituresPerItem($callback, $start, $end);
    }
}
