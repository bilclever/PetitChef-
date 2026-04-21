<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'account_status')) {
                $table->string('account_status')->default('active')->after('approval_status');
            }

            if (! Schema::hasColumn('users', 'account_status_reason')) {
                $table->text('account_status_reason')->nullable()->after('account_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'account_status_reason')) {
                $table->dropColumn('account_status_reason');
            }

            if (Schema::hasColumn('users', 'account_status')) {
                $table->dropColumn('account_status');
            }
        });
    }
};
