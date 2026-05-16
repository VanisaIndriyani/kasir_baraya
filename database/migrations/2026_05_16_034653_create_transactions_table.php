<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('transactions')) {
            return;
        }

        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('invoice', 32)->unique('uq_transactions_invoice');
            $table->enum('order_type', ['offline', 'online'])->default('offline');
            $table->string('platform', 20)->nullable();
            $table->enum('payment_method', ['cash', 'qris'])->default('cash');
            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('paid_amount')->default(0);
            $table->unsignedInteger('change_amount')->default(0);
            $table->dateTime('created_at')->useCurrent();

            $table->index('created_at', 'idx_transactions_created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
