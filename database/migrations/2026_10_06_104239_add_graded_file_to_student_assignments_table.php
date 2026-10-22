<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('student_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('student_assignments', 'graded_file_path')) {
                $table->string('graded_file_path')->nullable()->after('file_name');
            }
            if (!Schema::hasColumn('student_assignments', 'graded_file_name')) {
                $table->string('graded_file_name')->nullable()->after('graded_file_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_assignments', function (Blueprint $table) {
            $table->dropColumn(['graded_file_path', 'graded_file_name']);
        });
    }
};
