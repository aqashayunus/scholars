<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class BillingSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Sync Company Bank Info from finance settings if empty
        if (!Setting::get('billing.bank_name')) {
            Setting::set('billing.bank_name', 'Maybank Islamic Berhad', 'billing', 'Bank Name');
        }
        if (!Setting::get('billing.bank_account_name')) {
            Setting::set('billing.bank_account_name', 'Olympia Scholars Academy Sdn Bhd', 'billing', 'Bank Account Name');
        }
        if (!Setting::get('billing.bank_account_number')) {
            Setting::set('billing.bank_account_number', '5642 8901 2345', 'billing', 'Bank Account Number');
        }
        if (!Setting::get('billing.payment_instructions')) {
            Setting::set('billing.payment_instructions', 'Please include your Student Name & Invoice No in the transfer reference, then upload proof of payment.', 'billing', 'Additional Payment Instructions');
        }

        // 2. Populate realistic monthly fees for existing subjects
        Subject::where('education_type', 'kindergarten')->whereNull('monthly_fee')->update(['monthly_fee' => 50.00]);
        Subject::where('education_type', 'primary')->whereNull('monthly_fee')->update(['monthly_fee' => 60.00]);
        Subject::where('education_type', 'secondary')->where('form_year', 'Form 1–3')->whereNull('monthly_fee')->update(['monthly_fee' => 70.00]);
        Subject::where('education_type', 'secondary')->where('form_year', 'Form 4–5')->whereIn('name', ['Physics', 'Chemistry', 'Biology', 'Additional Mathematics', 'Computer Science'])->whereNull('monthly_fee')->update(['monthly_fee' => 90.00]);
        Subject::where('education_type', 'secondary')->where('form_year', 'Form 4–5')->whereNull('monthly_fee')->update(['monthly_fee' => 80.00]);

        // 3. Backfill line items and reminders for existing legacy invoices
        foreach (\App\Models\Invoice::all() as $invoice) {
            if ($invoice->items()->count() === 0) {
                \App\Models\InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'description' => $invoice->title ?: 'Tuition Fee',
                    'unit_price'  => $invoice->total_amount,
                    'quantity'    => 1,
                    'amount'      => $invoice->total_amount,
                ]);
            }
            if ($invoice->reminders()->count() === 0 && $invoice->student) {
                app(\App\Services\InvoiceService::class)->scheduleReminders($invoice, $invoice->student);
            }
        }
    }
}
