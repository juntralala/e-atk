<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opname_reasons', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->string('reason', 100)->unique();
            $table->dateTime('deleted_at')->nullable();
            $table->datetimes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opname_reasons');
    }
};
