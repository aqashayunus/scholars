<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add bank details to teachers table if not exists
        if (Schema::hasTable('teachers')) {
            Schema::table('teachers', function (Blueprint $table) {
                if (!Schema::hasColumn('teachers', 'bank_name')) {
                    $table->string('bank_name', 100)->nullable()->after('specialization');
                }
                if (!Schema::hasColumn('teachers', 'bank_account_number')) {
                    $table->string('bank_account_number', 50)->nullable()->after('bank_name');
                }
                if (!Schema::hasColumn('teachers', 'bank_account_holder')) {
                    $table->string('bank_account_holder', 255)->nullable()->after('bank_account_number');
                }
            });
        }

        // 2. Create teacher_class_claims table
        if (!Schema::hasTable('teacher_class_claims')) {
            Schema::create('teacher_class_claims', function (Blueprint $table) {
                $table->id();
                $table->string('claim_number', 50)->unique();
                $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
                $table->foreignId('live_class_id')->nullable()->constrained('live_classes')->nullOnDelete();
                $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
                $table->string('subject_name');
                $table->string('class_title');
                $table->date('class_date');
                $table->integer('duration_minutes')->default(60);
                $table->decimal('rate_per_class', 10, 2)->default(50.00);
                $table->decimal('amount', 10, 2)->default(50.00);
                $table->text('teacher_notes')->nullable();
                $table->string('proof_attachment_path')->nullable();
                $table->string('bank_name', 100)->nullable();
                $table->string('bank_account_number', 50)->nullable();
                $table->string('bank_account_holder', 255)->nullable();
                $table->string('status', 20)->default('pending'); // pending, approved, rejected, paid
                $table->text('admin_notes')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->string('payment_method', 100)->nullable();
                $table->string('payment_reference', 255)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_class_claims');

        if (Schema::hasTable('teachers')) {
            Schema::table('teachers', function (Blueprint $table) {
                $cols = [];
                if (Schema::hasColumn('teachers', 'bank_name')) $cols[] = 'bank_name';
                if (Schema::hasColumn('teachers', 'bank_account_number')) $cols[] = 'bank_account_number';
                if (Schema::hasColumn('teachers', 'bank_account_holder')) $cols[] = 'bank_account_holder';
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
