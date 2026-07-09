<?php

namespace App\Actions\StockAdjustment;

use App\Enums\StockAdjustmentStatus;
use App\Models\Item;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockAdjustmentWithApprovalAction
{
    public function __construct() {}

    public function handle($stockAdjustmentInput)
    {
        DB::transaction(function () use ($stockAdjustmentInput) {
            $userId = Auth::id();
            $stockAdjustment = StockAdjustment::create([
                'user_id' => $userId,
                'notes' => $stockAdjustmentInput['notes'] ?? null,
                'status' => StockAdjustmentStatus::PENDING,
            ]);

            foreach ($stockAdjustmentInput['items'] as $stockAdjustmentItem) {
                $item = Item::find($stockAdjustmentItem['item_id']);
                StockAdjustmentDetail::create([
                    'stock_adjustment_id' => $stockAdjustment->id,
                    'item_id' => $item->id,
                    'old_stock' => $item->stock,
                    'new_stock' => $item->stock - $stockAdjustmentItem['adjustment'],
                    'price' => $item->price,
                    'reason_id' => $stockAdjustmentItem['reason_id'],
                ]);
                $item->save();
            }
        });
    }
}
