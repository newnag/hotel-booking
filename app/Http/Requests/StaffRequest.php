<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StaffRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $staffId = $this->route('staff')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$staffId],
            'phone' => ['nullable', 'string', 'max:20'],
            'line_user_id' => ['nullable', 'string', 'max:255'],
            'role' => ['required', Rule::in(['staff', 'admin'])],
            'password' => [
                $this->isMethod('PUT') ? 'nullable' : 'required',
                'confirmed',
                Password::defaults(),
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'ชื่อ',
            'email' => 'อีเมล',
            'phone' => 'เบอร์โทรศัพท์',
            'line_user_id' => 'Line User ID',
            'role' => 'บทบาท',
            'password' => 'รหัสผ่าน',
        ];
    }
}
