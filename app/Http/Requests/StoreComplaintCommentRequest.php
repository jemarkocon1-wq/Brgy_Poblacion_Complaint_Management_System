<?php

namespace App\Http\Requests;

use App\Models\Complaint;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreComplaintCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $complaint = $this->route('complaint');

        return $complaint instanceof Complaint
            && $complaint->status !== 'closed'
            && ($this->user()?->can('comment', $complaint) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'body' => ['nullable', 'string', 'max:5000', 'required_without:attachments'],
            'attachments' => [
                $this->user()?->role === 'resident' ? 'prohibited' : 'nullable',
                'array',
                'max:5',
                'required_without:body',
            ],
            'attachments.*' => [
                'file',
                'mimetypes:image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/webm',
                'max:20480',
            ],
        ];
    }
}
