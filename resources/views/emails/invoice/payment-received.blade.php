@extends('emails.invoice.layout')

@section('content')
<p class="greeting">Dear {{ $recipientName }},</p>

<div class="alert-box alert-success">
    Great news! Your payment has been received and successfully verified. Your invoice has been marked as <strong>Paid</strong>.
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
        <span class="label">Receipt No.</span>
        <span class="value">{{ $receiptNumber }}</span>
    </div>
    <div class="info-row">
        <span class="label">Billing Period</span>
        <span class="value">{{ $billingPeriod }}</span>
    </div>
    <div class="info-row">
        <span class="label">Payment Date</span>
        <span class="value">{{ $paymentDate }}</span>
    </div>
    <div class="info-row">
        <span class="label">Status</span>
        <span class="value"><span class="paid-badge">PAID</span></span>
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
        <tr>
            <td style="color:#16a34a; font-weight:700;">Amount Paid</td>
            <td style="color:#16a34a; font-weight:700;">RM {{ $amountPaid }}</td>
        </tr>
        <tr>
            <td style="color:#64748b;">Outstanding</td>
            <td style="color:#64748b;">RM {{ $outstandingAmount }}</td>
        </tr>
    </tfoot>
</table>

<div class="cta-row">
    <a href="{{ $invoiceUrl }}" class="btn-primary">View Invoice &amp; Receipt</a>
</div>

<hr class="divider">

<p style="font-size: 14px; color: #64748b; line-height: 1.7;">
    Thank you for your prompt payment. Please keep this email as your payment confirmation. Your receipt number is <strong>{{ $receiptNumber }}</strong>.
</p>

<br>
<p style="font-size: 14px; color: #1a202c;">Regards,<br><strong>{{ $senderName }}</strong><br>Olympia Education Malaysia</p>
@endsection
