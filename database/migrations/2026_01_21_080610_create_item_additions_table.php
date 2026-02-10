<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_additions', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->string('user_id', 100);
            $table->date('addition_date');
            $table->dateTime('created_at');

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_additions');
    }
};
