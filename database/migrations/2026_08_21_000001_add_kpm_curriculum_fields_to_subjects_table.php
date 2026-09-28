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
        Schema::table('subjects', function (Blueprint $table) {
            $table->string('education_type', 30)->default('secondary')->after('slug'); // kindergarten, primary, secondary
            $table->string('stage', 50)->default('Upper Secondary')->after('education_type'); // Preschool, Primary, Lower Secondary, Upper Secondary
            $table->string('form_year', 30)->default('Form 4–5')->after('stage'); // Preschool, Year 1, Year 2, Year 3, Year 4, Year 5, Year 6, Form 1, Form 2, Form 3, Form 4, Form 5, Form 4–5, All Years
            $table->string('category', 50)->default('Core')->after('form_year'); // Core, Elective, Early Development, Skill & Applied, STEM Elective, Humanities Elective, Commerce Elective
            $table->string('stream_package', 50)->nullable()->after('category'); // STEM Pure Science, Commerce & Accounting, Humanities & Arts, STEM Computing, General
            $table->string('code', 30)->nullable()->after('stream_package'); // e.g. KPM-KSSR-BM-Y1, KPM-KSSM-AM-F4
            $table->integer('order')->default(0)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn([
                'education_type',
                'stage',
                'form_year',
                'category',
                'stream_package',
                'code',
                'order'
            ]);
        });
    }
};
