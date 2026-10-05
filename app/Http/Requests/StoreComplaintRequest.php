<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreComplaintRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user('resident')?->isRole('resident') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'category' => ['required', 'in:noise,sanitation,infrastructure,public_safety,other'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'location' => ['required', 'string', 'max:255'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'mimetypes:image/jpeg,image/png,image/webp,video/mp4,video/webm', 'max:51200'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                foreach ($this->file('attachments', []) as $index => $file) {
                    if ($file->isValid() && str_starts_with((string) $file->getMimeType(), 'image/') && $file->getSize() > 5 * 1024 * 1024) {
                        $validator->errors()->add("attachments.$index", 'Each image must be 5 MB or smaller.');
                    }
                }
            },
        ];
    }
}
