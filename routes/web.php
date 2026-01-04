<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\MeasurementUnitController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecipientController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SkuController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\Transaction\InTransactionController;
use App\Http\Controllers\Transaction\OutTransactionController;
use App\Http\Controllers\Transaction\TransactionHistoryController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::prefix('/api')->group(function () {
        Route::get('/roles', [RoleController::class, 'getAll']);
        Route::get('/users', [UserController::class, 'getUsers']);
        Route::post('/users', [UserController::class, 'addUser']);
        Route::put('/users/{id}', [UserController::class, 'editUser']);
        Route::delete('/users/{id}', [UserController::class, 'deleteUser']);
    });
    Route::get('/expenditures/skus', [DashboardController::class, 'getExpendituresPerSKU'])->name('expenditures.skus');
    Route::get('/expenditures/skus/export/xlsx', [DashboardController::class, 'toXlsx'])->name('expenditures.skus.export.xlsx');
    Route::get('/recipients', [RecipientController::class, 'page'])->name('recipients');
    Route::post('/recipients', [RecipientController::class, 'store']);
    Route::put('/recipients/{id}', [RecipientController::class, 'update']);
    Route::delete('/recipients/{id}', [RecipientController::class, 'destroy']);
    Route::get('/items', [ItemController::class, 'page'])->name('items');
    Route::post('/items', [ItemController::class, 'store']);
    Route::put('/items/{id}', [ItemController::class, 'update']);
    Route::delete('/items/{id}', [ItemController::class, 'destroy']);
    Route::get('/items/inbound', [InTransactionController::class, 'index'])->name('items.inbound');
    Route::post('/items/inbound', [InTransactionController::class, 'store']);
    Route::get('/items/outbound', [OutTransactionController::class, 'index'])->name('items.outbound');
    Route::post('/items/outbound', [OutTransactionController::class, 'store']);
    Route::get('/items/transactions/histories', [TransactionHistoryController::class, 'index'])->name('items.transactions.history');
    Route::get('/items/transactions/histories/export/xlsx', [TransactionHistoryController::class, 'toXlsx'])->name('items.transactions.history.export.xlsx');
    Route::get('/items/units', [MeasurementUnitController::class, 'page'])->name('items.units');
    Route::post('/items/units', [MeasurementUnitController::class, 'store']);
    Route::put('/items/units/{id}', [MeasurementUnitController::class, 'update']);
    Route::delete('/items/units/{id}', [MeasurementUnitController::class, 'destroy']);
    Route::get('/items/skus', [SkuController::class, 'page'])->name('items.skus');
    Route::post('/items/skus', [SkuController::class, 'create'])->name('items.skus.create');
    Route::put('/items/skus/{id}', [SkuController::class, 'update'])->name('items.skus.update');
    Route::delete('/items/skus/{id}', [SkuController::class, 'delete'])->name('items.skus.delete');
    Route::get('/items/skus/export/xlsx', [SkuController::class, 'toXlsx'])->name('items.skus.export.xlsx');
    Route::get('/items/skus/units/{skuId}', [MeasurementUnitController::class, 'getSupportedMueasurementUnitsBySkuId'])->name('items.skus.units.by-sku-id');
    Route::get('/items/stocks', [StockController::class, 'index'])->name('items.stocks');
    Route::post('/items/stocks', [StockController::class, 'store']);
    Route::put('/items/stocks/{id}', [StockController::class, 'update']);
    Route::delete('/items/stocks/{id}', [StockController::class, 'destroy']);
    Route::get('/items/stocks/export/xlsx', [StockController::class, 'toXlsx'])->name('items.stocks.export.xlsx');
    Route::get('/profile', [ProfileController::class, 'page'])->name('account.profile');
    Route::get('/settings', [SettingController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::get('/users', [UserController::class, 'index'])->name('users');
});

Route::get("/inspect", function (Request $request) {
    return response()->json([
        'user' => $request->user(),
        'session' => $request->session()->all()
    ]);
});

Route::get('/set-session', function (Request $request) {
    $key = $request->input('key');
    $value = $request->input('value', 'not set');
    $request->session()->put($key, $value);
    return "$key : $value";
});
Route::get('/get-session', function (Request $request) {
    return session()->all();
});

Route::inertia("/counter", "Counter");

    Route::get('/expenditures/items', [DashboardController::class, 'getExpendituresPerItem'])->name('expenditures.items');
    Route::get('/expenditures/items/export/xlsx', [DashboardController::class, 'toXlsxExpendituresPerItem'])->name('expenditures.items.export.xlsx');
