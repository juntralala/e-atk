<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_request_details', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->string('item_request_id', 100);
            $table->string('item_id', 100);
            $table->integer('requested_quantity');
            $table->integer('responded_quantity')->nullable();
            $table->decimal('price', 20, 2);
            $table->datetimes();

            $table->foreign('item_request_id')->references('id')->on('item_requests')->cascadeOnDelete();
            $table->foreign('item_id')->references('id')->on('items');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_request_details');
    }
};
