<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('aster_employee_id')->nullable()->after('user_id');
            $table->boolean('is_verified')->default(true)->after('aster_employee_id');
            $table->boolean('is_online')->default(true)->after('is_verified');
            $table->decimal('hourly_rate', 8, 2)->default(50.00)->after('is_online');
            $table->string('tier')->default('senior')->after('hourly_rate');
            $table->decimal('wallet_balance', 10, 2)->default(1450.00)->after('tier');
            $table->integer('acceptance_rate')->default(96)->after('wallet_balance');
            $table->integer('completion_rate')->default(98)->after('acceptance_rate');
            $table->integer('total_gigs_completed')->default(42)->after('completion_rate');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn([
                'aster_employee_id',
                'is_verified',
                'is_online',
                'hourly_rate',
                'tier',
                'wallet_balance',
                'acceptance_rate',
                'completion_rate',
                'total_gigs_completed',
            ]);
        });
    }
};
