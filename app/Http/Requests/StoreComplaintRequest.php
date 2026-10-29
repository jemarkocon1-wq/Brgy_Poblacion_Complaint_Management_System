<?php

namespace App\Http\Requests;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreComplaintRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->can('create', Complaint::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'resident_id' => [$this->user()?->role === 'resident' ? 'prohibited' : 'required', 'integer'],
            'title' => ['required', 'string', 'max:160'],
            'category' => ['required', 'in:civil,criminal_case,other'],
            'respondents' => ['required', 'string', 'max:2000'],
            'case_subject' => ['required', 'string', 'max:160'],
            'incident_date' => ['exclude_unless:category,criminal_case', 'required', 'date', 'before_or_equal:today'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'location' => ['required', 'string', 'max:255'],
            'relief_sought' => ['exclude_unless:category,civil', 'nullable', 'string', 'max:2000'],
            'witnesses' => ['exclude_unless:category,criminal_case', 'nullable', 'string', 'max:2000'],
            'assigned_staff_id' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['nullable', 'in:pending,approved,rejected,resolved,unsettled,closed'],
            'approved_at' => ['nullable', 'date'],
            'staff_responded_at' => ['nullable', 'date'],
            'resolved_at' => ['nullable', 'date'],
            'closed_at' => ['nullable', 'date'],
            'receipt_confirmed_at' => ['nullable', 'date'],
            'summons_ready_at' => ['nullable', 'date'],
            'attachments' => [
                $this->user()?->role === 'resident' ? 'prohibited' : 'nullable',
                'array',
                'max:5',
            ],
            'attachments.*' => ['file', 'mimetypes:image/jpeg,image/png,image/webp,video/mp4,video/webm', 'max:51200'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $actor = $this->user();
                $residentId = $this->input('resident_id');

                if ($actor !== null && in_array($actor->role, ['staff', 'admin'], true)
                    && ! $validator->errors()->has('resident_id')
                    && ! User::query()->whereKey($residentId)->where('role', 'resident')->where('is_active', true)->exists()) {
                    $validator->errors()->add('resident_id', 'Choose an active resident account.');
                }

                foreach ($this->file('attachments', []) as $index => $file) {
                    if ($file->isValid() && str_starts_with((string) $file->getMimeType(), 'image/') && $file->getSize() > 5 * 1024 * 1024) {
                        $validator->errors()->add("attachments.$index", 'Each image must be 5 MB or smaller.');
                    }
                }
            },
        ];
    }
}
