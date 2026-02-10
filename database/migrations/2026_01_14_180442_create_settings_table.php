<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->string('icon', 500)->nullable();
            $table->string('logo', 500)->nullable();
            $table->string('application_name', 255);
            $table->string('institution_name', 255);
            $table->string('institution_address', 255);
            $table->string('institution_phone', 255);
            $table->datetimes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
