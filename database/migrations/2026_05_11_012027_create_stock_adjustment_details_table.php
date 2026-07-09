<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustment_details', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->string('stock_adjustment_id', 100);
            $table->string('item_id', 100);
            $table->integer('old_stock');
            $table->integer('new_stock');
            $table->decimal('price', 20, 2);
            $table->string('reason_id', 100);
            $table->datetimes();

            $table->foreign('stock_adjustment_id')->references('id')->on('stock_adjustments');
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('reason_id')->references('id')->on('stock_adjustment_reasons');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustment_details');
    }
};
