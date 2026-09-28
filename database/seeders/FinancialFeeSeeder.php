<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\Setting;
use App\Models\Payment;

class FinancialFeeSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Company Bank Info & Gateway Settings (Public Bank Details & Encrypted Gateway defaults)
        Setting::set('company_bank_name', 'Maybank Islamic Berhad', 'finance', 'Company Bank Name');
        Setting::set('company_bank_account_name', 'Olympia Scholars Academy Sdn Bhd', 'finance', 'Company Account Name');
        Setting::set('company_bank_account_number', '5642 8901 2345', 'finance', 'Company Account Number');
        Setting::set('company_bank_instructions', 'Please include your Student Name & Invoice No in the transfer reference.', 'finance', 'Transfer Instructions');

        Setting::set('gateway_provider', 'mock_fpx', 'finance', 'Payment Gateway Provider');
        Setting::set('gateway_merchant_id', 'OSC_MERCHANT_8892', 'finance', 'Gateway Merchant ID');
        Setting::set('gateway_sandbox_mode', '1', 'finance', 'Gateway Sandbox Mode');

        // 2. Seed Fee Structures
        $fees = [
            [
                'name'            => 'SPM Core Monthly Tuition',
                'description'     => 'Monthly standard tuition covering Bahasa Melayu, English, Mathematics & Science.',
                'amount'          => 250.00,
                'education_level' => 'Form 4–5',
                'frequency'       => 'monthly',
                'is_active'       => true,
            ],
            [
                'name'            => 'PT3 Lower Secondary Monthly Tuition',
                'description'     => 'Comprehensive tuition for Form 1 to Form 3 core subjects.',
                'amount'          => 200.00,
                'education_level' => 'Form 1–3',
                'frequency'       => 'monthly',
                'is_active'       => true,
            ],
            [
                'name'            => 'Primary UPSR Foundation Tuition',
                'description'     => 'Primary mastery foundation program for Year 4 to Year 6.',
                'amount'          => 160.00,
                'education_level' => 'Year 4–6',
                'frequency'       => 'monthly',
                'is_active'       => true,
            ],
            [
                'name'            => 'Annual Digital Resource & Exam Drill Access',
                'description'     => 'Access to digital past-year SPM papers, video replay archive, and AI practice drills.',
                'amount'          => 120.00,
                'education_level' => 'All',
                'frequency'       => 'yearly',
                'is_active'       => true,
            ],
        ];

        foreach ($fees as $feeData) {
            FeeStructure::firstOrCreate(['name' => $feeData['name']], $feeData);
        }

        $spmFee = FeeStructure::where('name', 'SPM Core Monthly Tuition')->first();

        // 3. Seed Invoices for Students
        $students = Student::with('user')->get();
        foreach ($students as $index => $student) {
            // Outstanding Invoice (Current Month)
            $invNumber = 'INV-' . date('Y') . '-' . str_pad($student->id * 10 + 1, 5, '0', STR_PAD_LEFT);
            Invoice::firstOrCreate([
                'invoice_number' => $invNumber,
            ], [
                'student_id'       => $student->id,
                'fee_structure_id' => $spmFee?->id,
                'title'            => 'Tuition Fee – ' . date('F Y'),
                'description'      => 'Monthly tuition fee including interactive live classes and study materials.',
                'subtotal'         => 250.00,
                'discount_amount'  => 0.00,
                'total_amount'     => 250.00,
                'due_date'         => now()->addDays(14)->toDateString(),
                'status'           => 'outstanding',
            ]);

            // Paid Invoice (Previous Month)
            $paidInvNumber = 'INV-' . date('Y') . '-' . str_pad($student->id * 10 + 2, 5, '0', STR_PAD_LEFT);
            $paidInvoice = Invoice::firstOrCreate([
                'invoice_number' => $paidInvNumber,
            ], [
                'student_id'       => $student->id,
                'fee_structure_id' => $spmFee?->id,
                'title'            => 'Tuition Fee – ' . now()->subMonth()->format('F Y'),
                'description'      => 'Monthly tuition fee including interactive live classes and study materials.',
                'subtotal'         => 250.00,
                'discount_amount'  => 0.00,
                'total_amount'     => 250.00,
                'due_date'         => now()->subMonth()->toDateString(),
                'status'           => 'paid',
            ]);

            // Create corresponding paid payment record
            Payment::firstOrCreate([
                'invoice_id' => $paidInvoice->id,
            ], [
                'user_id'        => $student->user_id,
                'student_id'     => $student->id,
                'payer_id'       => $student->user_id,
                'payer_type'     => 'student',
                'description'    => $paidInvoice->title,
                'amount'         => 250.00,
                'payment_method' => 'fpx',
                'reference_no'   => 'TXN-FPX-' . strtoupper(substr(md5($paidInvoice->id . '-' . $student->id), 0, 8)),
                'receipt_number' => 'RCP-' . date('Y') . '-' . str_pad($paidInvoice->id, 5, '0', STR_PAD_LEFT),
                'status'         => 'paid',
                'paid_at'        => now()->subMonth()->addDays(2),
            ]);
        }
    }
}
