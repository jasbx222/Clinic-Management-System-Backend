<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'file_number' => $this->file_number,
            'date_of_birth' => $this->date_of_birth,
            'gender' => $this->gender,
            'blood_group' => $this->blood_group,
            'allergies' => $this->allergies,
            'chronic_diseases' => $this->chronic_diseases,
            'user' => new UserResource($this->whenLoaded('user')),
            'visits' => VisitResource::collection($this->whenLoaded('visits')),
            'invoices' => InvoiceResource::collection($this->whenLoaded('invoices')),
            'prescriptions' => PrescriptionResource::collection($this->whenLoaded('prescriptions')),
            'created_at' => $this->created_at,
        ];
    }
}
