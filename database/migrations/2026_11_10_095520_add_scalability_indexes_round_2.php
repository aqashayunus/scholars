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
        // Matches the dominant query shape across Student\AssignmentService,
        // Student\DashboardMetricsService, and Teacher\OverviewService:
        // StudentAssignment::where('student_id', ...)->where('status', ...).
        Schema::table('student_assignments', function (Blueprint $table) {
            $table->index(['student_id', 'status']);
        });

        // live_classes.scheduled_at is filtered/sorted on in every timetable
        // and "upcoming classes" query (TimetableService::eventsJson(),
        // ClassScheduleService, dashboard widgets) with no supporting index.
        Schema::table('live_classes', function (Blueprint $table) {
            $table->index('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_assignments', function (Blueprint $table) {
            $table->dropIndex(['student_id', 'status']);
        });

        Schema::table('live_classes', function (Blueprint $table) {
            $table->dropIndex(['scheduled_at']);
        });
    }
};
