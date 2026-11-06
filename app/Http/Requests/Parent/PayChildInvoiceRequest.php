<?php

namespace App\Http\Requests\Parent;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Services\Parent\ChildAccessService;
use Illuminate\Foundation\Http\FormRequest;

class PayChildInvoiceRequest extends FormRequest
{
    /**
     * Mirrors the controller's own Invoice::whereIn('student_id', $childIds)
     * ->findOrFail($id) scoping, moved here so a child that isn't the
     * parent's own 404s before the body runs, exactly as findOrFail did.
     */
    public function authorize(): bool
    {
        $childIds = app(ChildAccessService::class)->getParentChildren($this->user())->pluck('id')->toArray();

        Invoice::whereIn('student_id', $childIds)->findOrFail($this->route('id'));

        return true;
    }

    /**
     * An already-paid invoice must not fail on a missing bank-transfer proof
     * file -- the controller short-circuits with an "already paid" message
     * before ever reaching the validated payment flow, so no rules apply.
     */
    public function rules(): array
    {
        $childIds = app(ChildAccessService::class)->getParentChildren($this->user())->pluck('id')->toArray();
        $invoice = Invoice::whereIn('student_id', $childIds)->findOrFail($this->route('id'));

        if ($invoice->status === InvoiceStatus::Paid) {
            return [];
        }

        return [
            'proof_file' => ['required_if:payment_method,bank_transfer', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }
}
