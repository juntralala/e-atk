<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_addition_details', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->string('item_addition_id', 100);
            $table->string('item_id', 100);
            $table->integer('quantity');
            $table->decimal('price', 20, 2);
            $table->dateTime('created_at');

            $table->foreign('item_addition_id')->references('id')->on('item_additions');
            $table->foreign('item_id')->references('id')->on('items');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_addition_details');
    }
};
