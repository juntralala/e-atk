<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->string('name', 255);
            $table->string('unit_id', 100);
            $table->string('specification_name', 255);
            $table->unsignedInteger('stock')->default(0);
            $table->decimal('price', 20, 2)->default(0);
            $table->datetimes();
            $table->dateTime('deleted_at')->nullable();

            $table->foreign('unit_id')->references('id')->on('units');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
