<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_opname', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->string('user_id', 100);
            $table->string('responder_id', 100);
            $table->enum('status', ['pending', 'approved', 'rejected']);
            $table->date('opname_date');
            $table->datetimes();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('responder_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opname');
    }
};
