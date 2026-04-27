<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required_without:user_id|string|max:255',
            'phone' => 'required_without:user_id|string|max:255',
            'email' => 'nullable|email',
            'user_id' => 'nullable|exists:users,id',
            'date_of_birth' => 'nullable|date',
            'birth_date' => 'nullable|date',
            'gender' => 'required|in:male,female,other',
            'blood_group' => 'nullable|string',
            'allergies' => 'nullable|string',
            'chronic_diseases' => 'nullable|string',
        ];
    }
}
