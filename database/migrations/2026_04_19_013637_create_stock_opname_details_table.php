<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_opname_details', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->string('stock_opname_id', 100);
            $table->string('item_id', 100);
            $table->integer('system_stock');
            $table->integer('physical_stock');
            $table->string('reason_id', 100);
            $table->text('remark');
            $table->datetimes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opname_details');
    }
};
