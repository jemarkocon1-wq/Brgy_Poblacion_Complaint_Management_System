<?php

namespace App\Http\Requests;

use App\Models\Complaint;
use Illuminate\Foundation\Http\FormRequest;

class SaveComplaintSummonsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $complaint = $this->route('complaint');

        return $complaint instanceof Complaint
            && ($this->user()?->can('manageSummons', $complaint) ?? false);
    }

    /**
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'summons_case_number' => ['required', 'string', 'max:80'],
            'summons_for' => ['required', 'string', 'max:255'],
            'summons_hearing_date' => ['required', 'date_format:Y-m-d'],
            'summons_hearing_time' => ['required', 'date_format:H:i'],
            'summons_issued_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:summons_hearing_date'],
            'summons_signatory' => ['required', 'string', 'max:255'],
        ];
    }
}
