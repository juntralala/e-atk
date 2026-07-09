<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustment_reasons', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->string('reason', 100);
            $table->datetimes();
            $table->softDeletesDatetime();

            $table->index('reason');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustment_reasons');
    }
};
