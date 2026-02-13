<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUuid('created_by');
            $table->foreignUlid('outlet_id');
            $table->foreignUlid('inventory_item_id')->references('id')->on('inventory_items');
            $table->integer('quantity')->nullable();
            $table->integer('total')->nullable();
            $table->enum('type', ['in', 'out', 'correction']);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
    }
};
