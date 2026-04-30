<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'patient_id' => $this->patient_id,
            'appointment_id' => $this->appointment_id,
            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'tax' => $this->tax,
            'final_amount' => $this->final_amount,
            'status' => $this->status,
            'due_date' => $this->due_date,
            'patient' => new PatientResource($this->whenLoaded('patient')),
            'appointment' => new AppointmentResource($this->whenLoaded('appointment')),
            'items' => $this->whenLoaded('items'),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'created_at' => $this->created_at,
        ];
    }
}
