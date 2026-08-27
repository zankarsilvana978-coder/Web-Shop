<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sellers are a stateful business entity separate from users:
     * application status, store identity, balances and the optional
     * per-seller commission override live here.
     */
    public function up(): void
    {
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('store_name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('status')->default('pending');
            $table->string('rejection_reason')->nullable();
            $table->decimal('commission_rate', 5, 2)->nullable()->comment('Per-seller commission override');
            $table->decimal('balance', 10, 2)->default(0)->comment('Available for payout');
            $table->decimal('pending_balance', 10, 2)->default(0)->comment('Held until hold period ends');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sellers');
    }
};
