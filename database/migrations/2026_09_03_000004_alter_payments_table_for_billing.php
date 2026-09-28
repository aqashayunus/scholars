<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Alter payments table to match V1 billing spec.
 *
 * Status changes:
 *   OLD enum: paid, pending, overdue, cancelled
 *   NEW enum: pending, successful, failed, cancelled
 *
 * New columns:
 *   gateway_transaction_id — reference from payment gateway (future use)
 *   payment_proof_notes    — admin notes when verifying manual payment
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver !== 'sqlite') {
            DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM('pending','successful','failed','cancelled') NOT NULL DEFAULT 'pending'");
        } else {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('status', 20)->default('pending')->change();
            });
        }

        // Migrate old status values to new ones
        DB::table('payments')->where('status', 'paid')->update(['status' => 'successful']);
        DB::table('payments')->where('status', 'overdue')->update(['status' => 'pending']);

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'gateway_transaction_id')) {
                $table->string('gateway_transaction_id')->nullable()->after('reference_no')
                      ->comment('Transaction ID from online payment gateway (future use)');
            }
            if (!Schema::hasColumn('payments', 'payment_proof_notes')) {
                $table->text('payment_proof_notes')->nullable()->after('proof_file_path')
                      ->comment('Admin notes when reviewing manual bank transfer proof');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            foreach (['gateway_transaction_id', 'payment_proof_notes'] as $col) {
                if (Schema::hasColumn('payments', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        $driver = DB::getDriverName();
        if ($driver !== 'sqlite') {
            DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM('paid','pending','overdue','cancelled') NOT NULL DEFAULT 'pending'");
        }
    }
};
