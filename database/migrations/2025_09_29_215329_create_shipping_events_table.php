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
        Schema::create('shipping_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('carrier');
            $table->string('tracking_number');
            $table->timestamp('shipped_at')->nullable();
            $table->json('raw_payload');
            $table->string('signature');
            $table->timestamps();
            $table->unique(['order_id', 'tracking_number']); // ensure idempotency
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_events');
    }
};
