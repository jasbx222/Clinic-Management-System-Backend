<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
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
        'patient_id' => 'required|exists:patients,id',
        'appointment_id' => 'nullable|exists:appointments,id',
        'visit_id' => 'nullable|exists:visits,id',

        'subtotal' => 'required|numeric|min:0',
        'discount' => 'nullable|numeric|min:0',
        'tax' => 'nullable|numeric|min:0',

        // 'paid_amount' => 'nullable|numeric|min:0',

        // 'status' => 'nullable|in:unpaid,partially_paid,paid,cancelled',

        'notes' => 'nullable|string',
    ];
}
}
