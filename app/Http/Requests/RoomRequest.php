<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoomRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only admin can manage rooms
        return $this->user() && $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $roomId = $this->route('room')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:meeting_rooms,name,'.$roomId,
            ],
            'description' => 'nullable|string',
            'max_capacity' => 'required|integer|min:1|max:1000',
            'is_active' => 'boolean',
            'image' => [
                $this->isMethod('POST') ? 'nullable' : 'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:2048', // 2MB
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
            'name' => 'ชื่อห้องประชุม',
            'description' => 'คำอธิบาย',
            'max_capacity' => 'จำนวนที่นั่ง',
            'is_active' => 'สถานะ',
            'image' => 'รูปภาพ',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'กรุณากรอก:attribute',
            'name.unique' => ':attributeนี้ถูกใช้งานแล้ว',
            'max_capacity.required' => 'กรุณากรอก:attribute',
            'max_capacity.min' => ':attributeต้องมีอย่างน้อย :min ที่นั่ง',
            'max_capacity.max' => ':attributeต้องไม่เกิน :max ที่นั่ง',
            'image.image' => ':attributeต้องเป็นไฟล์รูปภาพ',
            'image.mimes' => ':attributeต้องเป็นไฟล์ประเภท :values',
            'image.max' => ':attributeต้องมีขนาดไม่เกิน 2MB',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert checkbox to boolean
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => $this->boolean('is_active'),
            ]);
        } else {
            // If checkbox is not checked, set to false
            $this->merge([
                'is_active' => false,
            ]);
        }
    }
}
