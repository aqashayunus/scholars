<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Backup of every row the admin "Level clean-up" tool removes or changes when
 * it reduces a multi-level student ("Form 4, Form 5") to one level. One row
 * per touched record, with the full original row as JSON, so any clean-up can
 * be checked or undone by hand.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('level_cleanup_backups')) {
            return;
        }

        Schema::create('level_cleanup_backups', function (Blueprint $table) {
            $table->id();
            $table->string('batch', 36)->index();          // one UUID per confirmed clean-up
            $table->unsignedBigInteger('student_id')->index();
            $table->unsignedBigInteger('admin_user_id')->nullable();
            $table->string('table_name', 64);
            $table->unsignedBigInteger('row_id')->nullable();
            $table->string('action', 32);                  // deleted | updated | created
            $table->json('original')->nullable();          // row before the change (null for created)
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('level_cleanup_backups');
    }
};
