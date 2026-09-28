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
            // Drop the ENUM and replace with a flexible VARCHAR
            DB::statement("ALTER TABLE `students` MODIFY `level` VARCHAR(100) NOT NULL DEFAULT 'SPM'");
        } else {
            Schema::table('students', function (Blueprint $table) {
                $table->string('level', 100)->default('SPM')->change();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            // Revert back to the original ENUM (existing data outside the enum will be truncated by MySQL)
            DB::statement("ALTER TABLE `students` MODIFY `level` ENUM('SPM','IGCSE','STPM','Foundation') NOT NULL DEFAULT 'SPM'");
        }
    }
};
