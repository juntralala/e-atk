<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\MeasurementUnit;
use App\Models\Recipient;
use App\Models\Sku;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Service\MeasurementUnitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OutTransactionController extends Controller
{
    public function __construct(
        private MeasurementUnitService $measurementUnitService
    ){}
    
    public function index()
    {
        return Inertia::render('OutboundItem', [
            'items' => Item::with('skus')->get(),
            'measurementUnits' => MeasurementUnit::all(),
            'recipients' => Recipient::all(),
        ]);
    }

    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'recipient_id' => 'required|string',
            'division' => 'required|string|max:255',
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string',
            'transaction_items' => 'required|array|min:1',
            'transaction_items.*.sku_id' => 'required|exists:skus,id',
            'transaction_items.*.unit_id' => 'required|exists:measurement_units,id',
            'transaction_items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();
            $transaction = Transaction::create([
                'type' => 'out',
                'recipient_id' => $validated['recipient_id'],
                'user_id' => $request->user()->id,
                'division' => $validated['division'] ?? null,
                'transaction_date' => Date::parse($validated['transaction_date']),
                'notes' => $validated['notes'] ?? null,
            ]);
            foreach ($validated['transaction_items'] as $item) {
                $sku = Sku::findOrFail($item['sku_id']);
                $conversion = $this->measurementUnitService->getConversion($item['unit_id'], $sku->id);
                $baseQuantity = $item['quantity'] * $conversion;
                if ($sku->quantity < $baseQuantity) {
                    throw new \Exception(
                        "Stok tidak mencukupi untuk barang '{$sku->item->name}'. " .
                        "Stok tersedia: {$sku->quantity} {$sku->item->baseMeasurementUnit->name}, " .
                        "Diminta: {$item['quantity']} {$sku->item->name}"
                    );
                }
                $sku->increment('quantity',  );
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'sku_id' => $item['sku_id'],
                    'measurement_unit_id' => $item['unit_id'],
                    'price' => $sku->price,
                    'quantity' => $item['quantity'],
                    'base_quantity' => $baseQuantity,
                ]);
            }
            DB::commit();
            return redirect()->back()->with('success', 'Transaksi barang keluar berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }
}