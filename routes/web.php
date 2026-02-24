<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ItemAdditionController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemRequestController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingPageController::class, 'showPage']);

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'loginPage'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

Route::middleware('auth')->group(function () {
    Route::get('/home', [HomeController::class, 'redirector'])->name('home');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'dashboardPage'])->name('dashboards');
    Route::can('administrator-petugas')->prefix('/api/dashboard')->group(function () {
        Route::get('/summary', [DashboardController::class, 'summary']);
        Route::get('/expenditures/monthly', [DashboardController::class, 'monthlyExpenditures']);
        Route::get('/expenditures/items', [DashboardController::class, 'expendituresPerItems']);
        Route::get('/expenditures/units', [DashboardController::class, 'expenditurePerUnit']);
        Route::get('/requests/recents', [DashboardController::class, 'recentItemRequests']);
        Route::get('/items/tops', [DashboardController::class, 'topItems']);
        Route::get('/items/requests/statuses/counts', [DashboardController::class, 'itemRequestStatuses'])
            ->name('api.dashboards.requests.status.count');
    });

    Route::prefix('/notifications')->name('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'getCurrentUserNotifications']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsReadNotification'])->name('.read');
        Route::get('/unread/exists', [NotificationController::class, 'isUnreadNotificationExists'])->name('.unread.exists');
    });

    Route::prefix('/users')->name('users')->group(function () {
        Route::get("/", [UserController::class, 'showPage'])->name('')
            ->can('viewAny', User::class);
        Route::post("/", [UserController::class, 'create'])->name('.create')
            ->can('create', User::class);
        Route::put("/{user}", [UserController::class, 'update'])->name('.update')
            ->middleware('can:update,user');
        Route::delete("/{user}", [UserController::class, 'delete'])->name('.delete')
            ->can('delete', 'user');
        Route::patch("/{user}/restore", [UserController::class, 'restore'])->name('.restore');
    });

    Route::get('/profile', [UserController::class, 'showProfile'])->name('profile');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');

    Route::can('administrator-petugas')->prefix('/units')->name('units')->group(function () {
        Route::get('/', [UnitController::class, 'showPage']);
        Route::post('/', [UnitController::class, 'createUnit'])->name('.create');
        Route::put('/{id}', [UnitController::class, 'updateUnit'])->name('.update');
        Route::delete('/{id}', [UnitController::class, 'deleteUnit'])->name('.delete');
    });

    Route::can('administrator-only')->prefix('/settings')->name('settings')->group(function () {
        Route::get('/', [SettingController::class, 'showPage']);
        Route::put('/', [SettingController::class, 'update'])->name('.update');
    });

    Route::prefix('/items')->name('items')->group(function () {
        Route::can('administrator-petugas-unit')->group(function () {
            Route::get('/', [ItemController::class, 'showPage']);
        });
        Route::can('administrator-petugas')->group(function () {
            Route::post('/', [ItemController::class, 'create'])->name('.create');
            Route::put('/{id}', [ItemController::class, 'update'])->name('.update');
            Route::delete('/{id}', [ItemController::class, 'delete'])->name('.delete');
            Route::get('/exports/view', [ItemController::class, 'reportPage'])->name('.exports.view');
            Route::get('/reports/xlsx', [ItemController::class, 'toXlsx'])->name('.exports.xlsx');
        });

        Route::can('administrator-petugas')->prefix('/additions')->name('.additions')->can('administrator-petugas')->group(function () {
            Route::get('/', [ItemAdditionController::class, 'showPage']);
            Route::post('/', [ItemAdditionController::class, 'create'])->name('.create');
            Route::get('/exports/xlsx', [ItemAdditionController::class, 'toXlsx'])->name('.exports.xlsx');
            Route::get('/exports/view', [ItemAdditionController::class, 'reportPage'])->name('.exports.view');
        });

        Route::prefix('/requests')->name('.requests')->group(function () {
            Route::can('administrator-unit')->group(function () {
                Route::post('/', [ItemRequestController::class, 'create'])->name('.create');
                Route::get('/form', [ItemRequestController::class, 'showItemRequestForm'])->name('.form');
            });
            Route::can('administrator-petugas-unit')->group(function () {
                Route::get('/', [ItemRequestController::class, 'showPage']);
                Route::put('/{itemRequest}', [ItemRequestController::class, 'update'])->name('.update');
                Route::delete('/{itemRequest}', [ItemRequestController::class, 'delete'])->name('.delete');
            });
            Route::can('administrator-petugas')->group(function () {
                Route::put('/{itemRequest}/accept', [ItemRequestController::class, 'accept'])->name('.accept');
                Route::put('/{itemRequest}/reject', [ItemRequestController::class, 'reject'])->name('.reject');
                Route::get('/exports/view', [ItemRequestController::class, 'reportPage'])->name('.exports.view');
                Route::get('/exports/xlsx', [ItemRequestController::class, 'toXlsx'])->name('.exports.xlsx');
            });
        });
    });

    Route::can('administrator-petugas-bendahara')->group(function () {
        Route::get('/expenditures/exports/view', [ItemController::class, 'itemExpenditureReport'])->name('items.expenditures.exports.view');
        Route::get('/expenditures/exports/xlsx', [ItemController::class, 'toExpenditureXlsx'])->name('items.expenditures.exports.xlsx');
        Route::get('/expenditures/units/exports/view', [ItemRequestController::class, 'unitExpenditureReport'])->name('expenditures.units.exports.view');
        Route::get('/expenditures/units/exports/xlsx', [ItemRequestController::class, 'toUnitExpenditureXlsx'])->name('expenditures.units.exports.xlsx');
    });

    Route::inertia('/stakeholders', 'StakeHolder')->name('stakeholders');
    Route::get('/roles', [RoleController::class, 'getRoles'])->name('roles');
});

Route::inertia('/counter', 'Counter');
