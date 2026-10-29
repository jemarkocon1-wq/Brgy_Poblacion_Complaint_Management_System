<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class ManageUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user('admin')?->isRole('admin') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'lowercase', 'email:rfc,filter', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', $this->isMethod('post') ? 'in:admin,resident,staff' : 'in:resident,staff'],
            'password' => [$this->isMethod('post') ? 'required' : 'nullable', 'confirmed', Password::min(8)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->sometimes('email', ['ends_with:@gmail.com'], fn (): bool => $this->input('role') === 'resident');
    }
}
