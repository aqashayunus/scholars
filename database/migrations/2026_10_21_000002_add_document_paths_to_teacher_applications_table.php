<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('teacher_applications')) {
            Schema::table('teacher_applications', function (Blueprint $table) {
                if (!Schema::hasColumn('teacher_applications', 'certificate_path')) {
                    $table->string('certificate_path')->nullable()->after('resume_path');
                }
                if (!Schema::hasColumn('teacher_applications', 'transcript_path')) {
                    $table->string('transcript_path')->nullable()->after('certificate_path');
                }
                if (!Schema::hasColumn('teacher_applications', 'additional_document_path')) {
                    $table->string('additional_document_path')->nullable()->after('transcript_path');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('teacher_applications')) {
            Schema::table('teacher_applications', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('teacher_applications', 'certificate_path')) {
                    $columns[] = 'certificate_path';
                }
                if (Schema::hasColumn('teacher_applications', 'transcript_path')) {
                    $columns[] = 'transcript_path';
                }
                if (Schema::hasColumn('teacher_applications', 'additional_document_path')) {
                    $columns[] = 'additional_document_path';
                }
                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
