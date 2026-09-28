@extends('emails.invoice.layout')

@section('content')
<p class="greeting">Dear {{ $recipientName }},</p>

<div class="alert-box alert-info">
    Your Scholars invoice has been issued successfully. Please make payment by the due date to avoid your invoice becoming overdue.
</div>

<div class="info-box">
    <div class="info-row">
        <span class="label">Student</span>
        <span class="value">{{ $studentName }}</span>
    </div>
    <div class="info-row">
        <span class="label">Invoice No.</span>
        <span class="value">{{ $invoiceNumber }}</span>
    </div>
    <div class="info-row">
        <span class="label">Billing Period</span>
        <span class="value">{{ $billingPeriod }}</span>
    </div>
    <div class="info-row">
        <span class="label">Issue Date</span>
        <span class="value">{{ $issueDate }}</span>
    </div>
    <div class="info-row">
        <span class="label">Due Date</span>
        <span class="value">{{ $dueDate }}</span>
    </div>
    <div class="info-row">
        <span class="label">Status</span>
        <span class="value"><span class="outstanding-badge">{{ $status }}</span></span>
    </div>
</div>

<h3 style="font-size: 14px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">Invoice Details</h3>

<table class="items-table">
    <thead>
        <tr>
            <th>Subject / Item</th>
            <th style="text-align:right;">Amount (RM)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
        <tr>
            <td>{{ $item->description }}</td>
            <td>{{ number_format($item->amount, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="total-row">
            <td>Total Amount</td>
            <td>RM {{ $totalAmount }}</td>
        </tr>
        @if((float) str_replace(',', '', $amountPaid) > 0)
        <tr>
            <td style="color:#16a34a;">Amount Paid</td>
            <td style="color:#16a34a;">RM {{ $amountPaid }}</td>
        </tr>
        @endif
        <tr>
            <td style="color:#dc2626;">Outstanding Amount</td>
            <td style="color:#dc2626;">RM {{ $outstandingAmount }}</td>
        </tr>
    </tfoot>
</table>

<div class="cta-row">
    <a href="{{ $invoiceUrl }}" class="btn-primary">View Invoice</a>
    <a href="{{ $invoiceUrl }}" class="btn-secondary">Pay Now</a>
</div>

@if($bankName || $bankAccountNumber)
<div class="bank-box">
    <h4>Bank Transfer Payment Details</h4>
    @if($bankName)
    <div class="bank-detail"><strong>Bank:</strong> {{ $bankName }}</div>
    @endif
    @if($bankAccountName)
    <div class="bank-detail"><strong>Account Name:</strong> {{ $bankAccountName }}</div>
    @endif
    @if($bankAccountNumber)
    <div class="bank-detail"><strong>Account Number:</strong> {{ $bankAccountNumber }}</div>
    @endif
    @if($paymentInstructions)
    <div class="bank-detail" style="margin-top:10px; padding-top:10px; border-top:1px solid #e2e8f0;">{{ $paymentInstructions }}</div>
    @endif
    <div class="bank-detail" style="margin-top:10px; color:#64748b; font-size:13px;">
        After transferring, please upload your proof of payment through your Scholars account.
    </div>
</div>
@endif

<hr class="divider">

<p style="font-size: 14px; color: #64748b; line-height: 1.7;">
    Thank you for choosing Olympia Scholars. If you have any questions about this invoice, please contact our administration team.
</p>

<br>
<p style="font-size: 14px; color: #1a202c;">Regards,<br><strong>{{ $senderName }}</strong><br>Olympia Education Malaysia</p>
@endsection
