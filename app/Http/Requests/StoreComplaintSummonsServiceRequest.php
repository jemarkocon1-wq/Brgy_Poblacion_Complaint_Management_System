<?php

namespace App\Http\Requests;

use App\Models\Complaint;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreComplaintSummonsServiceRequest extends FormRequest
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
            'served_at' => ['required', 'date_format:Y-m-d\TH:i', 'before_or_equal:now'],
            'server_name' => ['required', 'string', 'max:255'],
            'recipient_name' => ['exclude_unless:result,served,refused', 'required', 'string', 'max:255'],
            'result' => ['required', 'in:served,refused,unable_to_serve'],
            'signer_type' => ['required', 'in:respondent,server,none'],
            'signer_name' => ['exclude_unless:signer_type,respondent,server', 'required', 'string', 'max:255'],
            'refusal_response' => ['exclude_unless:result,refused', 'required', 'string', 'max:2000'],
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'extensions:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['result', 'signer_type'])) {
                    return;
                }

                if ($this->input('result') === 'served' && $this->input('signer_type') === 'none') {
                    $validator->errors()->add('signer_type', 'A served summons must be signed by the respondent or the server.');
                }

                if ($this->input('result') === 'refused' && $this->input('signer_type') === 'respondent') {
                    $validator->errors()->add('signer_type', 'A respondent cannot be recorded as signing after refusing to sign.');
                }

                if ($this->input('result') === 'unable_to_serve' && $this->input('signer_type') !== 'none') {
                    $validator->errors()->add('signer_type', 'No signer can be recorded when service was not completed.');
                }
            },
        ];
    }
}
