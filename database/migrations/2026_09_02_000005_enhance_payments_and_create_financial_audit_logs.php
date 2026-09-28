<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('invoice_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('student_id')->nullable()->after('invoice_id')->constrained()->nullOnDelete();
            $table->foreignId('payer_id')->nullable()->after('student_id')->constrained('users')->nullOnDelete();
            $table->enum('payer_type', ['student', 'parent', 'admin'])->default('student')->after('payer_id');
            $table->string('proof_file_path')->nullable()->after('payment_method');
            $table->string('receipt_number')->nullable()->unique()->after('status');
            $table->foreignId('verified_by')->nullable()->after('receipt_number')->constrained('users')->nullOnDelete();
        });

        Schema::create('financial_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action'); // view_credentials, update_settings, approve_payment, reject_payment, issue_invoice, refund
            $table->string('target_type')->nullable(); // Invoice, Payment, GatewaySettings
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('details')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_audit_logs');

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropForeign(['student_id']);
            $table->dropForeign(['payer_id']);
            $table->dropForeign(['verified_by']);
            $table->dropColumn([
                'invoice_id',
                'student_id',
                'payer_id',
                'payer_type',
                'proof_file_path',
                'receipt_number',
                'verified_by',
            ]);
        });
    }
};
