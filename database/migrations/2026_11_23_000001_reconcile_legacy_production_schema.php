<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Brings a database whose schema was built outside this repo's migration
 * history (olympianscholars.com: partly imported from a dump, partly migrated
 * from the retired pre-2026-11-18 `main` branch) up to the schema that
 * khai-QA-refactor's migrations produce on a fresh database.
 *
 * On that host, 30 migrations whose tables/columns already existed were
 * baselined (marked as run) instead of executed, so the enum values and
 * indexes those migrations would have created are missing. This migration
 * adds exactly those, and nothing else.
 *
 * Fully idempotent: every index is guarded by Schema::hasIndex(), and the
 * enum MODIFYs restate the exact definition the original migrations create.
 * On any database built from this repo's migrations (test host, CI, local)
 * it is a no-op.
 *
 * Deliberately NOT done here: foreign key constraints that the legacy schema
 * lacks (it has plain indexes instead). Adding them can fail on orphaned rows
 * and needs its own audited migration.
 */
return new class extends Migration
{
    /**
     * Missing non-foreign-key indexes, as [table, index name, columns, unique].
     * Derived from a schema diff of the production copy against a fresh
     * migrate on 2026-11-23. The 5 unique keys were checked for duplicates
     * against production data (0 found) before being added here.
     */
    private const INDEXES = [
        ['assignments', 'assignments_is_interactive_pdf_index', ['is_interactive_pdf'], false],
        ['attendance_correction_requests', 'attendance_correction_requests_student_id_live_class_id_index', ['student_id', 'live_class_id'], false],
        ['attendance_events', 'attendance_events_student_id_live_class_id_index', ['student_id', 'live_class_id'], false],
        ['chat_audit_logs', 'chat_audit_logs_child_id_created_at_index', ['child_id', 'created_at'], false],
        ['chat_audit_logs', 'chat_audit_logs_viewer_id_created_at_index', ['viewer_id', 'created_at'], false],
        ['chat_messages', 'chat_messages_conversation_id_id_index', ['conversation_id', 'id'], false],
        ['chat_message_reads', 'chat_message_reads_message_id_user_id_unique', ['message_id', 'user_id'], true],
        ['chat_message_reads', 'chat_message_reads_user_id_read_at_index', ['user_id', 'read_at'], false],
        ['chat_typing_indicators', 'chat_typing_indicators_conversation_id_user_id_unique', ['conversation_id', 'user_id'], true],
        ['conversations', 'conversations_context_type_context_id_index', ['context_type', 'context_id'], false],
        ['conversation_participants', 'conversation_participants_conversation_id_user_id_unique', ['conversation_id', 'user_id'], true],
        ['conversation_participants', 'conversation_participants_user_id_archived_at_index', ['user_id', 'archived_at'], false],
        ['fee_structures', 'fee_structures_education_level_status_index', ['education_level', 'status'], false],
        ['fee_structures', 'fee_structures_effective_from_effective_until_index', ['effective_from', 'effective_until'], false],
        ['fee_structures', 'fee_structures_package_type_status_is_active_index', ['package_type', 'status', 'is_active'], false],
        ['fee_structure_items', 'fee_structure_items_fee_structure_id_sort_order_index', ['fee_structure_id', 'sort_order'], false],
        ['fee_structure_price_tiers', 'fee_structure_price_tiers_fee_structure_id_sort_order_index', ['fee_structure_id', 'sort_order'], false],
        ['fee_structure_subjects', 'fee_structure_subjects_fee_structure_id_subject_id_unique', ['fee_structure_id', 'subject_id'], true],
        ['student_assignment_submissions', 'idx_sub_assignment', ['assignment_id'], false],
        ['student_assignment_submissions', 'idx_sub_student_status', ['student_id', 'status'], false],
        ['submission_pages', 'idx_sub_page_num', ['submission_id', 'page_number'], false],
        ['submission_pages', 'uq_sub_page', ['submission_id', 'page_number'], true],
    ];

    public function up(): void
    {
        $this->reconcileEnums();

        foreach (self::INDEXES as [$table, $name, $columns, $unique]) {
            if (! Schema::hasTable($table) || Schema::hasIndex($table, $name)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($name, $columns, $unique) {
                $unique
                    ? $blueprint->unique($columns, $name)
                    : $blueprint->index($columns, $name);
            });
        }
    }

    /**
     * Restate the enum definitions the original create migrations produce.
     * The legacy schema has older, shorter value lists, so the app's
     * FeeStructure::TYPE_CUSTOM, FeeStructureItem::TYPE_ASSESSMENT/DEPOSIT and
     * chat monitoring audit actions cannot be stored there.
     */
    private function reconcileEnums(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return; // SQLite (tests) stores enums as CHECK-constrained text from the original migrations.
        }

        $definitions = [
            'fee_structures' => "MODIFY COLUMN `package_type` ENUM('individual','fixed_bundle','pick_n','tiered','custom') NOT NULL DEFAULT 'individual'",
            'fee_structure_items' => "MODIFY COLUMN `item_type` ENUM('registration','tuition','material','resource','assessment','deposit','other') NOT NULL DEFAULT 'tuition'",
            'chat_audit_logs' => "MODIFY COLUMN `action` ENUM('conversation_viewed','message_viewed','attachment_viewed','monitoring_session_started','monitoring_session_ended') NOT NULL",
        ];

        foreach ($definitions as $table => $alter) {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE `{$table}` {$alter}");
            }
        }
    }

    /**
     * Intentionally a no-op: on fresh databases these indexes and enum values
     * belong to the original migrations, so dropping them here would break
     * those migrations' own rollback. Restore from the pre-deploy backup
     * (~/backups/scholars_db/) to undo on the legacy host.
     */
    public function down(): void
    {
        //
    }
};
