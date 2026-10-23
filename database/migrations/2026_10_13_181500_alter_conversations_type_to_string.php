<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `conversations` MODIFY COLUMN `type` VARCHAR(50) NOT NULL DEFAULT 'direct'");
        } else {
            Schema::table('conversations', function (Blueprint $table) {
                $table->string('type', 50)->default('direct')->change();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `conversations` MODIFY COLUMN `type` ENUM('direct','student_teacher','parent_teacher','parent_admin','student_admin','teacher_admin','support','system') NOT NULL DEFAULT 'direct'");
        }
    }
};
