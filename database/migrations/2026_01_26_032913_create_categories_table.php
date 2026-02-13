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
        Schema::create('categories', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('outlet_id');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['outlet_id', 'slug']);
        });

        Schema::create('category_product', function (Blueprint $table) {
            $table->foreignUlid('product_id');
            $table->foreignUlid('category_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_product');
        Schema::dropIfExists('categories');
    }
};
