<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // Ouvert/fermé manuellement par le cuisinier
            if (! Schema::hasColumn('users', 'shop_is_open')) {
                $table->boolean('shop_is_open')->default(true)->after('rejection_reason');
            }
            // Heure de clôture automatique (format HH:MM, ex: "14:00")
            if (! Schema::hasColumn('users', 'shop_closes_at')) {
                $table->string('shop_closes_at', 5)->nullable()->after('shop_is_open');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'shop_closes_at')) {
                $table->dropColumn('shop_closes_at');
            }
            if (Schema::hasColumn('users', 'shop_is_open')) {
                $table->dropColumn('shop_is_open');
            }
        });
    }
};
