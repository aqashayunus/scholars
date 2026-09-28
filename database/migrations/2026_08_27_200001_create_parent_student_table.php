<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('relationship', 30)->default('guardian');
            $table->boolean('is_primary')->default(true);
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->unique(['parent_user_id', 'student_id']);
        });

        // Seed pivot from existing students.parent_user_id — preserves all existing relationships
        $students = DB::table('students')->whereNotNull('parent_user_id')->get();
        foreach ($students as $student) {
            DB::table('parent_student')->insertOrIgnore([
                'parent_user_id' => $student->parent_user_id,
                'student_id'     => $student->id,
                'relationship'   => 'guardian',
                'is_primary'     => true,
                'status'         => 'active',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_student');
    }
};
