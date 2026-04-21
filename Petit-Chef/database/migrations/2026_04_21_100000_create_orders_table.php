<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('cook_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['recue', 'preparation', 'prete', 'livree'])->default('recue');
            $table->unsignedInteger('total')->default(0);
            $table->string('pickup_time')->nullable();
            $table->timestamps();
        });

        Schema::create('order_dish', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dish_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('unit_price');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_dish');
        Schema::dropIfExists('orders');
    }
};
