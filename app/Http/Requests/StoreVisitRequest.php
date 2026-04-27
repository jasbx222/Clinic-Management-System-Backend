<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreVisitRequest extends FormRequest
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
            'appointment_id' => 'nullable|exists:appointments,id',
            'patient_id' => 'required_without:appointment_id|exists:patients,id',
            'doctor_id' => 'nullable|exists:users,id',
            'chief_complaint' => 'required|string',
            'history' => 'nullable|string',
            'examination' => 'nullable|string',
        ];
    }
}
