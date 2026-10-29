<?php

namespace App\Http\Requests;

use App\Models\Complaint;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreComplaintMeetingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $complaint = $this->route('complaint');

        return $complaint instanceof Complaint
            && ($this->user()?->can('manageCaseRecords', $complaint) ?? false);
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'meeting_type' => ['required', 'in:mediation,hearing'],
            'meeting_date' => ['required', 'date', 'before_or_equal:today'],
            'participants' => ['nullable', 'string', 'max:2000'],
            'minutes' => ['required', 'string', 'min:10', 'max:10000'],
            'outcome' => ['required', 'in:amicable_settlement,unsettled'],
            'settlement_terms' => ['exclude_unless:outcome,amicable_settlement', 'required', 'string', 'min:5', 'max:5000'],
        ];
    }
}
