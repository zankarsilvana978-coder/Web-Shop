<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Immutable accounting ledger. Every earning, commission,
     * payout and refund must be logged here. Balances are always
     * reconstructible from this table.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payout_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->decimal('amount', 10, 2)->comment('Signed value: positive credit, negative debit');
            $table->string('description')->nullable();
            $table->timestamp('available_at')->nullable()->comment('When held funds become releasable');
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            $table->index(['seller_id', 'type']);
            $table->index(['type', 'released_at']);
            $table->index('available_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
