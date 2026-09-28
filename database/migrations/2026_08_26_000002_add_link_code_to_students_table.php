<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('link_code', 16)->nullable()->unique()->after('total_points');
            $table->string('parent_phone', 30)->nullable()->after('link_code');
        });

        // Generate link_code for any existing students
        $students = DB::table('students')->whereNull('link_code')->get();
        foreach ($students as $student) {
            do {
                $code = 'OS-' . strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6));
            } while (DB::table('students')->where('link_code', $code)->exists());

            DB::table('students')->where('id', $student->id)->update(['link_code' => $code]);
        }
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['link_code', 'parent_phone']);
        });
    }
};
