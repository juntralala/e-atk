<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_requests', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->string('requester_id', 100);
            $table->enum('status', ['pending', 'accepted', 'rejected']);
            $table->date('request_date');
            $table->string('responder_id', 100)->nullable();
            $table->string('responder_notes', 100)->nullable();
            $table->date('response_date')->nullable();
            $table->dateTime('responded_at')->nullable();
            $table->datetimes();

            $table->foreign('requester_id')->references('id')->on('users');
            $table->foreign('responder_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_requests');
    }
};
