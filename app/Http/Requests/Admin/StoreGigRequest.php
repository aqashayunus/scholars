<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreGigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'required|integer|min:15|max:240',
            'payout_amount' => 'required|numeric|min:0',
            'surge_bonus' => 'nullable|numeric|min:0',
            'gig_type' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ];
    }
}
