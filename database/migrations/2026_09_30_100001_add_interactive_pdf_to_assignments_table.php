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
        Schema::table('assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('assignments', 'is_interactive_pdf')) {
                $table->boolean('is_interactive_pdf')->default(false)->after('questions_data');
                $table->index('is_interactive_pdf');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            if (Schema::hasColumn('assignments', 'is_interactive_pdf')) {
                $table->dropIndex(['is_interactive_pdf']);
                $table->dropColumn('is_interactive_pdf');
            }
        });
    }
};
