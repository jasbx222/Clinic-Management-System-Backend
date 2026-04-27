<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePublicAppointmentRequest extends FormRequest
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
            'service_id' => 'required|exists:services,id',
            'doctor_id' => 'required|exists:users,id',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'patient.full_name' => 'required|string|max:255',
            'patient.phone' => 'required|string|max:20',
            'patient.gender' => 'required|in:male,female,other',
            'patient.birth_date' => 'required|date|before:today',
            'patient.reason' => 'nullable|string',
            'patient.notes' => 'nullable|string',
            'patient.email' => 'nullable|email',
        ];
    }
}
