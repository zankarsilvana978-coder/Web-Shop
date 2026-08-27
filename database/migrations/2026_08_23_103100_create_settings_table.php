<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Single-row site configuration editable from admin settings page. */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->default('Soukelkom');
            $table->string('support_email')->default('support@soukelkom.test');
            $table->decimal('global_commission_rate', 5, 2)->default(10.00);
            $table->decimal('shipping_flat_rate', 10, 2)->default(5.00);
            $table->decimal('payout_min', 10, 2)->default(50.00);
            $table->unsignedInteger('ship_deadline_hours')->default(48);
            $table->unsignedInteger('earning_hold_days')->default(14);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
