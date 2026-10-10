<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Stock ledger: one row per stock movement.
     *
     * quantity rules:
     *  - stock_in / stock_out : always stored as a POSITIVE number (type gives direction)
     *  - adjustment           : stored SIGNED (physical count - system count)
     */
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();

            // Products with history cannot be deleted (audit trail must survive).
            $table->foreignId('product_id')->constrained()->restrictOnDelete();

            // Who acted. If the user account is removed, keep the row but blank the user.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('type', ['stock_in', 'stock_out', 'adjustment']);
            $table->integer('quantity');
            $table->unsignedInteger('balance_after')->nullable(); // snapshot, set by observer
            $table->string('reference_number', 100)->nullable();   // PO / SO number
            $table->string('reason', 50)->nullable();              // adjustment reason
            $table->text('notes')->nullable();
            $table->timestamps();

            // Product history and date-range filtering
            $table->index(['product_id', 'created_at']);
            // Type filter on the transactions index
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
