<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Alter invoices table to match V1 billing spec.
 *
 * Status changes:
 *   OLD enum: outstanding, pending_verification, paid, overdue, cancelled
 *   NEW enum: draft, issued, partially_paid, paid, overdue, cancelled
 *
 * Data migration:
 *   outstanding         → issued
 *   pending_verification → issued
 *
 * New columns added:
 *   issued_date    — date invoice was formally issued
 *   billing_period — human-readable period label (e.g. "September 2026")
 *   amount_paid    — running total of successful payments applied to this invoice
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver !== 'sqlite') {
            // Step 1: Expand enum to include BOTH old and new values
            // so we can safely migrate data without violating constraints.
            DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM(
                'outstanding','pending_verification','paid','overdue','cancelled',
                'draft','issued','partially_paid'
            ) NOT NULL DEFAULT 'outstanding'");

            // Step 2: Migrate old status values to new ones
            DB::table('invoices')
                ->whereIn('status', ['outstanding', 'pending_verification'])
                ->update(['status' => 'issued']);

            // Step 3: Now switch to the clean new enum
            DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('draft','issued','partially_paid','paid','overdue','cancelled') NOT NULL DEFAULT 'issued'");
        } else {
            // SQLite: simple string field (no enum enforcement at DB level)
            Schema::table('invoices', function (Blueprint $table) {
                $table->string('status', 30)->default('issued')->change();
            });
            DB::table('invoices')
                ->whereIn('status', ['outstanding', 'pending_verification'])
                ->update(['status' => 'issued']);
        }

        // Step 4: Add new columns
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'issued_date')) {
                $table->date('issued_date')->nullable()->after('due_date')
                      ->comment('Date the invoice was formally issued (status changed to issued)');
            }
            if (!Schema::hasColumn('invoices', 'billing_period')) {
                $table->string('billing_period', 30)->nullable()->after('issued_date')
                      ->comment('Human-readable billing period, e.g. "September 2026"');
            }
            if (!Schema::hasColumn('invoices', 'amount_paid')) {
                $table->decimal('amount_paid', 10, 2)->default(0.00)->after('total_amount')
                      ->comment('Running total of successful payments applied to this invoice');
            }
        });

        // Step 5: Back-fill issued_date for existing invoices
        DB::table('invoices')
            ->whereNull('issued_date')
            ->update(['issued_date' => DB::raw('DATE(created_at)')]);
    }

    public function down(): void
    {
        // Remove added columns
        Schema::table('invoices', function (Blueprint $table) {
            $columns = ['issued_date', 'billing_period', 'amount_paid'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('invoices', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        $driver = DB::getDriverName();
        if ($driver !== 'sqlite') {
            // Revert enum
            DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('outstanding','pending_verification','paid','overdue','cancelled') NOT NULL DEFAULT 'outstanding'");
            // Revert status data
            DB::table('invoices')->where('status', 'issued')->update(['status' => 'outstanding']);
            DB::table('invoices')->where('status', 'partially_paid')->update(['status' => 'outstanding']);
            DB::table('invoices')->where('status', 'draft')->update(['status' => 'outstanding']);
        }
    }
};
