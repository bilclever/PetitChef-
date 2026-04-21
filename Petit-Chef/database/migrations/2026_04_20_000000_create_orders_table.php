<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('cook_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('recue');
            $table->unsignedInteger('total_price')->default(0);
            $table->dateTime('pickup_time')->nullable();
            $table->string('fulfillment_type')->default('pickup');
            $table->string('payment_method')->default('cash');
            $table->string('payment_status')->default('pending');
            $table->boolean('is_paid')->default(false);
            $table->string('payment_reference')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'created_at']);
            $table->index(['cook_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
