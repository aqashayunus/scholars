<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE `student_attendances` MODIFY `status` VARCHAR(32) NOT NULL DEFAULT 'pending_verification'");
        }

        Schema::table('student_attendances', function (Blueprint $table) {
            if (DB::getDriverName() === 'sqlite') {
                $table->string('status', 32)->default('pending_verification')->change();
            }
            if (!Schema::hasColumn('student_attendances', 'left_at')) {
                $table->timestamp('left_at')->nullable()->after('joined_at');
            }
            if (!Schema::hasColumn('student_attendances', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('left_at');
            }
            if (!Schema::hasColumn('student_attendances', 'verified_by')) {
                $table->foreignId('verified_by')->nullable()->after('verified_at')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('student_attendances', 'remarks')) {
                $table->text('remarks')->nullable()->after('verified_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_attendances', function (Blueprint $table) {
            if (Schema::hasColumn('student_attendances', 'verified_by')) {
                $table->dropForeign(['verified_by']);
                $table->dropColumn('verified_by');
            }
            if (Schema::hasColumn('student_attendances', 'remarks')) {
                $table->dropColumn('remarks');
            }
            if (Schema::hasColumn('student_attendances', 'verified_at')) {
                $table->dropColumn('verified_at');
            }
            if (Schema::hasColumn('student_attendances', 'left_at')) {
                $table->dropColumn('left_at');
            }
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE `student_attendances` MODIFY `status` ENUM('attended','absent','late','excused') NOT NULL DEFAULT 'attended'");
        }
    }
};
