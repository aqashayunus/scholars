@extends('emails.invoice.layout')

@section('content')
<p class="greeting">Dear {{ $recipientName }},</p>

<div class="alert-box {{ $isOverdue ? 'alert-danger' : ($reminderType === 'due_today' ? 'alert-warning' : 'alert-info') }}">
    {!! $headline !!}
    @if(!$isOverdue)
        Please make payment before the due date.
    @else
        Please settle the outstanding amount as soon as possible.
    @endif
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
        <span class="label">{{ $isOverdue ? 'Original Due Date' : 'Due Date' }}</span>
        <span class="value">{{ $dueDate }}</span>
    </div>
    <div class="info-row">
        <span class="label">Outstanding Amount</span>
        <span class="value" style="color:{{ $isOverdue ? '#dc2626' : '#d97706' }};">RM {{ $outstandingAmount }}</span>
    </div>
    <div class="info-row">
        <span class="label">Status</span>
        <span class="value">
            @if($isOverdue)
                <span class="overdue-badge">{{ $status }}</span>
            @else
                <span class="outstanding-badge">{{ $status }}</span>
            @endif
        </span>
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
            <td style="color:#dc2626; font-weight:700;">Outstanding</td>
            <td style="color:#dc2626; font-weight:700;">RM {{ $outstandingAmount }}</td>
        </tr>
    </tfoot>
</table>

<div class="cta-row">
    <a href="{{ $invoiceUrl }}" class="btn-primary">{{ $isOverdue ? 'Pay Now — Overdue' : 'Pay Now' }}</a>
    <a href="{{ $invoiceUrl }}" class="btn-secondary">View Invoice</a>
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
</div>
@endif

<hr class="divider">

<p style="font-size: 13px; color: #94a3b8; line-height: 1.7;">
    If you have already made the payment, please allow time for your payment status to be updated in your Scholars account.
    @if($isOverdue)
    If you believe this notice was sent in error, please contact the Scholars administration team.
    @endif
</p>

<br>
<p style="font-size: 14px; color: #1a202c;">Regards,<br><strong>{{ $senderName }}</strong><br>Olympia Education Malaysia</p>
@endsection
