<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->string('user_id', 100);
            $table->string('stock_opname_id')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected']);
            $table->text('notes')->nullable();
            $table->string('responder_id', 100)->nullable();
            $table->text('reposnder_notes')->nullable();
            $table->dateTime('responded_at')->nullable();
            $table->datetimes();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('stock_opname_id')->references('id')->on('stock_opname');
            $table->foreign('responder_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
