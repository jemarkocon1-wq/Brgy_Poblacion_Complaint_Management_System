<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use App\Models\User;

class TransitionComplaintRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('manageStatus', $this->route('complaint')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:2000'],
            'assigned_staff_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->sometimes('reason', ['required'], fn (): bool => $this->routeIs('*.reject'));
        $validator->after(function (Validator $validator): void {
            $staffId = $this->input('assigned_staff_id');

            if ($staffId && ! User::query()->whereKey($staffId)->where('role', 'staff')->where('is_active', true)->exists()) {
                $validator->errors()->add('assigned_staff_id', 'Choose an active staff account.');
            }
        });
    }
}
