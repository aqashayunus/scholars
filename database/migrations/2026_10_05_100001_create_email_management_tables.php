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
        // 1. Email Templates
        if (!Schema::hasTable('email_templates')) {
            Schema::create('email_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('key')->unique();
                $table->string('category')->index(); // greetings, payment, restriction, class, academic, renewal, support, general
                $table->string('subject');
                $table->longText('body');
                $table->string('banner_url')->nullable();
                $table->string('cta_label')->nullable();
                $table->string('cta_url')->nullable();
                $table->string('status')->default('active')->index(); // active, inactive, archived
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 2. Email Template Versions
        if (!Schema::hasTable('email_template_versions')) {
            Schema::create('email_template_versions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('email_template_id')->constrained('email_templates')->cascadeOnDelete();
                $table->integer('version_number');
                $table->string('subject');
                $table->longText('body');
                $table->string('cta_label')->nullable();
                $table->string('cta_url')->nullable();
                $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // 3. Email Campaigns (Blasts)
        if (!Schema::hasTable('email_campaigns')) {
            Schema::create('email_campaigns', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('category')->default('general')->index();
                $table->text('description')->nullable();
                $table->string('subject');
                $table->longText('body');
                $table->string('cta_label')->nullable();
                $table->string('cta_url')->nullable();
                $table->foreignId('template_id')->nullable()->constrained('email_templates')->nullOnDelete();
                $table->string('audience_type')->index();
                $table->json('audience_filters')->nullable();
                $table->integer('total_recipients')->default(0);
                $table->integer('sent_count')->default(0);
                $table->integer('failed_count')->default(0);
                $table->string('status')->default('draft')->index(); // draft, scheduled, queued, processing, completed, cancelled, failed
                $table->dateTime('scheduled_at')->nullable();
                $table->dateTime('started_at')->nullable();
                $table->dateTime('completed_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 4. Email Campaign Recipients
        if (!Schema::hasTable('email_campaign_recipients')) {
            Schema::create('email_campaign_recipients', function (Blueprint $table) {
                $table->id();
                $table->foreignId('email_campaign_id')->constrained('email_campaigns')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('email')->index();
                $table->string('name')->nullable();
                $table->string('recipient_type')->default('custom'); // parent, student, teacher, custom
                $table->foreignId('related_student_id')->nullable()->constrained('students')->nullOnDelete();
                $table->string('status')->default('pending')->index(); // pending, sent, failed
                $table->text('error_message')->nullable();
                $table->dateTime('sent_at')->nullable();
                $table->timestamps();
            });
        }

        // 5. Email Automations
        if (!Schema::hasTable('email_automations')) {
            Schema::create('email_automations', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->string('name');
                $table->string('category')->index(); // greetings, payment, restriction, class, academic, renewal, support, general
                $table->string('trigger_event');
                $table->text('description')->nullable();
                $table->foreignId('email_template_id')->nullable()->constrained('email_templates')->nullOnDelete();
                $table->boolean('is_enabled')->default(true)->index();
                $table->integer('delay_minutes')->default(0);
                $table->json('recipient_rules')->nullable();
                $table->dateTime('last_triggered_at')->nullable();
                $table->integer('success_count')->default(0);
                $table->integer('failure_count')->default(0);
                $table->timestamps();
            });
        }

        // 6. Email Automation Executions
        if (!Schema::hasTable('email_automation_executions')) {
            Schema::create('email_automation_executions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('email_automation_id')->constrained('email_automations')->cascadeOnDelete();
                $table->json('trigger_payload')->nullable();
                $table->string('idempotency_key')->nullable()->index();
                $table->string('status')->index(); // success, failed, skipped
                $table->string('recipient_email');
                $table->text('error_message')->nullable();
                $table->dateTime('executed_at');
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // 7. Email Logs
        if (!Schema::hasTable('email_logs')) {
            Schema::create('email_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('email_campaign_id')->nullable()->constrained('email_campaigns')->nullOnDelete();
                $table->foreignId('email_template_id')->nullable()->constrained('email_templates')->nullOnDelete();
                $table->foreignId('email_automation_id')->nullable()->constrained('email_automations')->nullOnDelete();
                $table->foreignId('recipient_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('recipient_email')->index();
                $table->string('recipient_name')->nullable();
                $table->string('recipient_type')->nullable()->index(); // parent, student, teacher, admin, custom
                $table->foreignId('related_student_id')->nullable()->constrained('students')->nullOnDelete();
                $table->foreignId('related_invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
                $table->string('subject');
                $table->string('category')->index();
                $table->text('body_preview')->nullable();
                $table->string('status')->default('queued')->index(); // queued, sent, delivered, failed
                $table->string('provider_message_id')->nullable();
                $table->integer('attempts')->default(1);
                $table->text('error_message')->nullable();
                $table->dateTime('queued_at')->nullable();
                $table->dateTime('sent_at')->nullable();
                $table->dateTime('delivered_at')->nullable();
                $table->timestamps();
            });
        }

        // 8. Email Suppressions
        if (!Schema::hasTable('email_suppressions')) {
            Schema::create('email_suppressions', function (Blueprint $table) {
                $table->id();
                $table->string('email')->unique();
                $table->string('reason')->default('unsubscribe'); // unsubscribe, bounce, manual
                $table->dateTime('suppressed_at');
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_suppressions');
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('email_automation_executions');
        Schema::dropIfExists('email_automations');
        Schema::dropIfExists('email_campaign_recipients');
        Schema::dropIfExists('email_campaigns');
        Schema::dropIfExists('email_template_versions');
        Schema::dropIfExists('email_templates');
    }
};
