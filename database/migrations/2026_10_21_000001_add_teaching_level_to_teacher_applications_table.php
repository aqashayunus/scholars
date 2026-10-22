<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('teacher_applications') && !Schema::hasColumn('teacher_applications', 'teaching_level')) {
            Schema::table('teacher_applications', function (Blueprint $table) {
                $table->string('teaching_level')->nullable()->after('preferred_subjects');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('teacher_applications') && Schema::hasColumn('teacher_applications', 'teaching_level')) {
            Schema::table('teacher_applications', function (Blueprint $table) {
                $table->dropColumn('teaching_level');
            });
        }
    }
};
