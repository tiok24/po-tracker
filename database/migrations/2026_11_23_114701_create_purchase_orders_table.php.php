<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('customer_name');
            $table->string('product_name');
            $table->unsignedInteger('order_quantity');
            $table->unsignedTinyInteger('progress')->default(0);
            $table->date('po_date');
            $table->date('production_start')->nullable();
            $table->date('estimated_finish')->nullable();
            $table->date('actual_finish')->nullable();
            $table->string('order_status')->default('Waiting')->index();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};