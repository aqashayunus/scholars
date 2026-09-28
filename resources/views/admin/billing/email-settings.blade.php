<x-admin-layout>
    <x-slot name="title">Email Reminder Settings</x-slot>

    <div class="space-y-6 max-w-3xl">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-heading font-black text-2xl text-gray-900">Email Reminder Settings</h2>
                <p class="text-xs text-gray-500 font-medium mt-0.5">Configure automated invoice reminder emails sent to parents and students.</p>
            </div>
            <a href="{{ route('admin.billing.dashboard') }}" class="text-sm text-indigo-600 hover:underline font-semibold">← Billing Dashboard</a>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm font-medium">✓ {{ session('success') }}</div>
        @endif

        <form action="{{ route('admin.billing.email-settings.update') }}" method="POST" class="space-y-6">
            @csrf

            {{-- ── Sender & Billing Config ── --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="font-bold text-gray-900">Billing Configuration</h3>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email Sender Name</label>
                            <input type="text" name="billing_email_sender_name"
                                   value="{{ $settings['billing.email_sender_name'] ?? 'Olympia Scholars' }}"
                                   class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-indigo-300 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Invoice Due Days (after issue)</label>
                            <input type="number" name="billing_invoice_due_days" min="1" max="90"
                                   value="{{ $settings['billing.invoice_due_days'] ?? '7' }}"
                                   class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-indigo-300 outline-none">
                            <p class="text-xs text-gray-400 mt-1">Default: 7 days</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Bank Transfer Details ── --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="font-bold text-gray-900">Bank Transfer Payment Details</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Displayed in invoice emails and on payment pages. Do NOT include sensitive credentials here.</p>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Bank Name</label>
                            <input type="text" name="billing_bank_name"
                                   value="{{ $settings['billing.bank_name'] ?? '' }}"
                                   placeholder="e.g. Maybank"
                                   class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-indigo-300 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Account Name</label>
                            <input type="text" name="billing_bank_account_name"
                                   value="{{ $settings['billing.bank_account_name'] ?? '' }}"
                                   placeholder="e.g. Olympia Education Sdn Bhd"
                                   class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-indigo-300 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Account Number</label>
                            <input type="text" name="billing_bank_account_number"
                                   value="{{ $settings['billing.bank_account_number'] ?? '' }}"
                                   placeholder="e.g. 5641-2345-6789"
                                   class="w-full font-mono text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-indigo-300 outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Additional Payment Instructions</label>
                        <textarea name="billing_payment_instructions" rows="3"
                                  placeholder="e.g. Please use your invoice number as the transfer reference..."
                                  class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-indigo-300 outline-none resize-none">{{ $settings['billing.payment_instructions'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            {{-- ── Reminder Toggles ── --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="font-bold text-gray-900">Email Reminder Schedule</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Toggle individual reminder emails on or off. Changes take effect on the next daily scheduler run.</p>
                </div>
                <div class="divide-y divide-gray-50">
                    @php
                        $reminders = [
                            ['key' => 'billing_reminder_invoice_issued', 'setting_key' => 'billing.reminder_invoice_issued', 'label' => 'Invoice Issued', 'desc' => 'Sent immediately when an invoice is generated', 'timing' => 'Immediate'],
                            ['key' => 'billing_reminder_due_7_days',     'setting_key' => 'billing.reminder_due_7_days',     'label' => '7 Days Before Due', 'desc' => 'Friendly reminder sent 7 days before due date', 'timing' => '7 days before'],
                            ['key' => 'billing_reminder_due_3_days',     'setting_key' => 'billing.reminder_due_3_days',     'label' => '3 Days Before Due', 'desc' => 'Second reminder sent 3 days before due date', 'timing' => '3 days before'],
                            ['key' => 'billing_reminder_due_today',      'setting_key' => 'billing.reminder_due_today',      'label' => 'Due Today', 'desc' => 'Final reminder on the due date', 'timing' => 'Due date'],
                            ['key' => 'billing_reminder_overdue_1_day',  'setting_key' => 'billing.reminder_overdue_1_day',  'label' => '1 Day Overdue', 'desc' => 'Overdue notice 1 day after due date', 'timing' => '+1 day'],
                            ['key' => 'billing_reminder_overdue_3_days', 'setting_key' => 'billing.reminder_overdue_3_days', 'label' => '3 Days Overdue', 'desc' => 'Second overdue notice', 'timing' => '+3 days'],
                            ['key' => 'billing_reminder_overdue_7_days', 'setting_key' => 'billing.reminder_overdue_7_days', 'label' => '7 Days Overdue', 'desc' => 'Final overdue notice — action required', 'timing' => '+7 days'],
                        ];
                    @endphp

                    @foreach($reminders as $reminder)
                    <div class="px-6 py-4 flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-mono font-bold bg-gray-100 text-gray-600">{{ $reminder['timing'] }}</span>
                                <p class="font-semibold text-sm text-gray-900">{{ $reminder['label'] }}</p>
                            </div>
                            <p class="text-xs text-gray-400 mt-0.5 ml-14">{{ $reminder['desc'] }}</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="{{ $reminder['key'] }}" class="sr-only peer"
                                   {{ ($settings[$reminder['setting_key']] ?? '1') == '1' ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm px-6 py-3 rounded-xl transition-colors shadow-sm">
                    Save Settings
                </button>
                <a href="{{ route('admin.billing.dashboard') }}" class="text-sm text-gray-500 hover:text-gray-700 font-medium">Cancel</a>
            </div>
        </form>
    </div>
</x-admin-layout>
