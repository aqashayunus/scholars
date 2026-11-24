<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the foreign keys that a fresh migrate creates but the legacy
 * production schema (olympianscholars.com, partly built from a dump and from
 * the retired pre-2026-11-18 main branch) never had: 72 of 121. See
 * docs/CICD.md §9.3.
 *
 * A foreign key can only be added once no row points at a missing parent, so
 * orphans are resolved first, following each key's own ON DELETE intent:
 *  - nullable column  -> the dangling reference is cleared, the row is kept
 *  - otherwise        -> the orphan row is deleted (its parent is already gone,
 *                        exactly what ON DELETE CASCADE would have done)
 * Resolution repeats until a full pass changes nothing, because deleting an
 * orphan can orphan rows one level down (e.g. subject_topics -> subtopics).
 *
 * Every row that is deleted or changed, including rows that the keys ALREADY
 * present would cascade-delete or null alongside it, is first copied in full
 * (JSON) into `legacy_fk_cleanup_backup`. Nothing is removed without a copy.
 *
 * Sign-off (2026-11-24): orphan clean-up with backup approved, including
 * teacher #24 (user account already deleted) together with its 3 courses and
 * their enrolments.
 *
 * Fully idempotent and a no-op wherever the keys already exist (test host,
 * CI, fresh databases), and on non-MySQL drivers (SQLite tests get the keys
 * from the original create migrations).
 */
return new class extends Migration
{
    /** [table, column, referenced table, referenced column, on delete] */
    private const FOREIGN_KEYS = [
        ['attendance_correction_requests', 'live_class_id', 'live_classes', 'id', 'cascade'],
        ['attendance_correction_requests', 'reviewer_id', 'users', 'id', 'set null'],
        ['attendance_correction_requests', 'student_attendance_id', 'student_attendances', 'id', 'set null'],
        ['attendance_correction_requests', 'student_id', 'students', 'id', 'cascade'],
        ['attendance_events', 'actor_id', 'users', 'id', 'set null'],
        ['attendance_events', 'live_class_id', 'live_classes', 'id', 'cascade'],
        ['attendance_events', 'student_attendance_id', 'student_attendances', 'id', 'set null'],
        ['attendance_events', 'student_id', 'students', 'id', 'cascade'],
        ['chat_audit_logs', 'child_id', 'users', 'id', 'cascade'],
        ['chat_audit_logs', 'conversation_id', 'conversations', 'id', 'cascade'],
        ['chat_audit_logs', 'viewer_id', 'users', 'id', 'cascade'],
        ['chat_messages', 'conversation_id', 'conversations', 'id', 'cascade'],
        ['chat_messages', 'reply_to_id', 'chat_messages', 'id', 'set null'],
        ['chat_messages', 'sender_id', 'users', 'id', 'cascade'],
        ['chat_message_attachments', 'message_id', 'chat_messages', 'id', 'cascade'],
        ['chat_message_reads', 'message_id', 'chat_messages', 'id', 'cascade'],
        ['chat_message_reads', 'user_id', 'users', 'id', 'cascade'],
        ['chat_typing_indicators', 'conversation_id', 'conversations', 'id', 'cascade'],
        ['chat_typing_indicators', 'user_id', 'users', 'id', 'cascade'],
        ['conversations', 'created_by', 'users', 'id', 'cascade'],
        ['conversation_participants', 'conversation_id', 'conversations', 'id', 'cascade'],
        ['conversation_participants', 'user_id', 'users', 'id', 'cascade'],
        ['email_automations', 'email_template_id', 'email_templates', 'id', 'set null'],
        ['email_automation_executions', 'email_automation_id', 'email_automations', 'id', 'cascade'],
        ['email_campaigns', 'created_by', 'users', 'id', 'set null'],
        ['email_campaigns', 'template_id', 'email_templates', 'id', 'set null'],
        ['email_campaign_recipients', 'email_campaign_id', 'email_campaigns', 'id', 'cascade'],
        ['email_campaign_recipients', 'related_student_id', 'students', 'id', 'set null'],
        ['email_campaign_recipients', 'user_id', 'users', 'id', 'set null'],
        ['email_logs', 'email_automation_id', 'email_automations', 'id', 'set null'],
        ['email_logs', 'email_campaign_id', 'email_campaigns', 'id', 'set null'],
        ['email_logs', 'email_template_id', 'email_templates', 'id', 'set null'],
        ['email_logs', 'recipient_user_id', 'users', 'id', 'set null'],
        ['email_logs', 'related_invoice_id', 'invoices', 'id', 'set null'],
        ['email_logs', 'related_student_id', 'students', 'id', 'set null'],
        ['email_templates', 'created_by', 'users', 'id', 'set null'],
        ['email_templates', 'updated_by', 'users', 'id', 'set null'],
        ['email_template_versions', 'changed_by', 'users', 'id', 'set null'],
        ['email_template_versions', 'email_template_id', 'email_templates', 'id', 'cascade'],
        ['fee_structures', 'parent_fee_structure_id', 'fee_structures', 'id', 'set null'],
        ['fee_structures', 'student_id', 'students', 'id', 'set null'],
        ['fee_structure_items', 'fee_structure_id', 'fee_structures', 'id', 'cascade'],
        ['fee_structure_price_tiers', 'fee_structure_id', 'fee_structures', 'id', 'cascade'],
        ['fee_structure_subjects', 'fee_structure_id', 'fee_structures', 'id', 'cascade'],
        ['fee_structure_subjects', 'subject_id', 'subjects', 'id', 'cascade'],
        ['live_classes', 'rescheduled_by', 'users', 'id', 'set null'],
        ['student_assignments', 'assignment_id', 'assignments', 'id', 'cascade'],
        ['student_assignments', 'student_id', 'students', 'id', 'cascade'],
        ['student_assignment_submissions', 'assignment_id', 'assignments', 'id', 'cascade'],
        ['student_assignment_submissions', 'student_assignment_id', 'student_assignments', 'id', 'cascade'],
        ['student_assignment_submissions', 'student_id', 'students', 'id', 'cascade'],
        ['student_attendances', 'live_class_id', 'live_classes', 'id', 'cascade'],
        ['student_attendances', 'student_id', 'students', 'id', 'cascade'],
        ['student_attendances', 'verified_by', 'users', 'id', 'set null'],
        ['student_quizzes', 'quiz_id', 'quizzes', 'id', 'cascade'],
        ['student_quizzes', 'student_id', 'students', 'id', 'cascade'],
        ['student_subtopic_progress', 'student_id', 'students', 'id', 'cascade'],
        ['student_subtopic_progress', 'subtopic_id', 'subject_subtopics', 'id', 'cascade'],
        ['student_topic_masteries', 'student_id', 'students', 'id', 'cascade'],
        ['student_topic_masteries', 'subject_id', 'subjects', 'id', 'cascade'],
        ['subject_subtopics', 'topic_id', 'subject_topics', 'id', 'cascade'],
        ['subject_topics', 'subject_id', 'subjects', 'id', 'cascade'],
        ['submission_pages', 'submission_id', 'student_assignment_submissions', 'id', 'cascade'],
        ['teachers', 'user_id', 'users', 'id', 'cascade'],
        ['teacher_applications', 'user_id', 'users', 'id', 'cascade'],
        ['teacher_earnings', 'teacher_id', 'teachers', 'id', 'cascade'],
        ['teacher_earnings', 'teaching_gig_id', 'teaching_gigs', 'id', 'set null'],
        ['teacher_subject', 'subject_id', 'subjects', 'id', 'cascade'],
        ['teacher_subject', 'teacher_id', 'teachers', 'id', 'cascade'],
        ['teaching_gigs', 'assigned_teacher_id', 'teachers', 'id', 'set null'],
        ['teaching_gigs', 'subject_id', 'subjects', 'id', 'set null'],
        ['user_notifications', 'user_id', 'users', 'id', 'cascade'],
    ];

    private const BACKUP_TABLE = 'legacy_fk_cleanup_backup';

    /** @var array<string, int> action => rows */
    private array $summary = [];

    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        $pending = array_values(array_filter(self::FOREIGN_KEYS, fn (array $fk) => $this->needsForeignKey(...$fk)));
        if ($pending === []) {
            return;
        }

        $this->ensureBackupTable();

        // Resolve orphans until a full pass changes nothing.
        do {
            $changed = 0;
            foreach ($pending as $fk) {
                $changed += $this->resolveOrphans(...$fk);
            }
        } while ($changed > 0);

        foreach ($pending as [$table, $column, $parentTable, $parentColumn, $onDelete]) {
            Schema::table($table, function (Blueprint $blueprint) use ($column, $parentTable, $parentColumn, $onDelete) {
                $blueprint->foreign($column)->references($parentColumn)->on($parentTable)->onDelete($onDelete);
            });
        }

        Log::info('[migration] Legacy foreign keys added.', ['added' => count($pending), 'rows' => $this->summary]);
    }

    /**
     * Intentionally a no-op: on fresh databases these keys belong to the
     * original create migrations. Restore removed rows from
     * legacy_fk_cleanup_backup or the pre-deploy dump if ever needed.
     */
    public function down(): void
    {
        //
    }

    private function needsForeignKey(string $table, string $column, string $parentTable, string $parentColumn, string $onDelete): bool
    {
        if (! Schema::hasTable($table) || ! Schema::hasTable($parentTable) || ! Schema::hasColumn($table, $column)) {
            return false;
        }

        foreach (Schema::getForeignKeys($table) as $foreignKey) {
            if ($foreignKey['columns'] === [$column] && $foreignKey['foreign_table'] === $parentTable) {
                return false;
            }
        }

        return true;
    }

    /**
     * Clear or delete the rows of $table whose $column points at a missing
     * $parentTable row. Returns how many rows were changed.
     */
    private function resolveOrphans(string $table, string $column, string $parentTable, string $parentColumn, string $onDelete): int
    {
        $orphans = DB::table($table.' as child')
            ->select('child.*')
            ->whereNotNull('child.'.$column)
            ->whereNotExists(function ($query) use ($parentTable, $parentColumn, $column) {
                $query->select(DB::raw(1))
                    ->from($parentTable.' as parent')
                    ->whereColumn('parent.'.$parentColumn, 'child.'.$column);
            })
            ->get();

        if ($orphans->isEmpty()) {
            return 0;
        }

        $reason = "{$table}.{$column} -> missing {$parentTable}.{$parentColumn}";
        $nullable = $this->isNullable($table, $column);

        foreach ($orphans as $orphan) {
            $row = (array) $orphan;

            if ($nullable) {
                $this->backup($table, 'nulled', $reason, $row);
                $this->rowQuery($table, $row)->update([$column => null]);
                $this->summary['nulled'] = ($this->summary['nulled'] ?? 0) + 1;

                continue;
            }

            $this->backupCascade($table, [$row], $reason);
            $this->backup($table, 'deleted', $reason, $row);
            $this->rowQuery($table, $row)->delete();
            $this->summary['deleted'] = ($this->summary['deleted'] ?? 0) + 1;
        }

        return $orphans->count();
    }

    /**
     * Before rows of $table are deleted, copy every row that the foreign keys
     * already present would cascade-delete or null with them, recursively.
     *
     * @param  list<array<string, mixed>>  $rows
     */
    private function backupCascade(string $table, array $rows, string $reason): void
    {
        $references = DB::select(
            'SELECT k.table_name AS child_table, k.column_name AS child_column, k.referenced_column_name AS parent_column, r.delete_rule
               FROM information_schema.key_column_usage k
               JOIN information_schema.referential_constraints r
                 ON r.constraint_schema = k.constraint_schema AND r.constraint_name = k.constraint_name
              WHERE k.constraint_schema = DATABASE() AND k.referenced_table_name = ?',
            [$table]
        );

        foreach ($references as $reference) {
            $reference = array_change_key_case((array) $reference, CASE_LOWER);
            $values = array_values(array_filter(array_column($rows, $reference['parent_column']), fn ($value) => $value !== null));
            if ($values === []) {
                continue;
            }

            $children = DB::table($reference['child_table'])->whereIn($reference['child_column'], $values)->get()
                ->map(fn ($child) => (array) $child)->all();
            if ($children === []) {
                continue;
            }

            $cascades = strtoupper($reference['delete_rule']) === 'CASCADE';
            foreach ($children as $child) {
                $this->backup($reference['child_table'], $cascades ? 'cascade-deleted' : 'cascade-nulled', $reason, $child);
            }
            $this->summary[$cascades ? 'cascade-deleted' : 'cascade-nulled'] = ($this->summary[$cascades ? 'cascade-deleted' : 'cascade-nulled'] ?? 0) + count($children);

            if ($cascades) {
                $this->backupCascade($reference['child_table'], $children, $reason);
            }
        }
    }

    /**
     * Query matching exactly this row: by primary key when the table has an
     * id, otherwise by all of its column values (e.g. teacher_subject).
     *
     * @param  array<string, mixed>  $row
     */
    private function rowQuery(string $table, array $row): Builder
    {
        if (array_key_exists('id', $row)) {
            return DB::table($table)->where('id', $row['id']);
        }

        $query = DB::table($table);
        foreach ($row as $column => $value) {
            $value === null ? $query->whereNull($column) : $query->where($column, $value);
        }

        return $query;
    }

    private function isNullable(string $table, string $column): bool
    {
        foreach (Schema::getColumns($table) as $definition) {
            if ($definition['name'] === $column) {
                return (bool) $definition['nullable'];
            }
        }

        return false;
    }

    /** @param  array<string, mixed>  $row */
    private function backup(string $table, string $action, string $reason, array $row): void
    {
        DB::table(self::BACKUP_TABLE)->insert([
            'source_table' => $table,
            'action' => $action,
            'reason' => $reason,
            'row_data' => json_encode($row, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
            'created_at' => now(),
        ]);
    }

    private function ensureBackupTable(): void
    {
        if (Schema::hasTable(self::BACKUP_TABLE)) {
            return;
        }

        Schema::create(self::BACKUP_TABLE, function (Blueprint $table) {
            $table->id();
            $table->string('source_table', 100)->index();
            $table->string('action', 30);
            $table->string('reason', 255);
            $table->longText('row_data');
            $table->timestamp('created_at')->nullable();
        });
    }
};
