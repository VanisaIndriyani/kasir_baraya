<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('transaction_items')) {
            return;
        }

        Schema::create('transaction_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('transaction_id');
            $table->unsignedInteger('product_id')->nullable();
            $table->string('product_name', 120);
            $table->unsignedInteger('price')->default(0);
            $table->unsignedInteger('qty')->default(1);
            $table->unsignedInteger('subtotal')->default(0);

            $table->index('transaction_id', 'idx_items_transaction');
            $table->index('product_id', 'idx_items_product');

            $table->foreign('transaction_id', 'fk_items_transaction')
                ->references('id')->on('transactions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('product_id', 'fk_items_product')
                ->references('id')->on('products')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
    }
};
