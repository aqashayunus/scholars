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
                if (!Schema::hasColumn('teacher_applications', 'ic_number')) {
                    $table->string('ic_number', 50)->nullable()->after('phone');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('teacher_applications')) {
            Schema::table('teacher_applications', function (Blueprint $table) {
                if (Schema::hasColumn('teacher_applications', 'ic_number')) {
                    $table->dropColumn('ic_number');
                }
            });
        }
    }
};
