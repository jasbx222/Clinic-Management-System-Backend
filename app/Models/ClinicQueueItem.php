<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicQueueItem extends Model
{
    protected $fillable = [
        'appointment_id',
        'patient_id',
        'doctor_id',
        'queue_number',
        'status',
        'priority',
        'estimated_wait_minutes',
        'called_at',
        'started_at',
        'billing_at',
        'completed_at',
        'skipped_at',
    ];

    protected function casts(): array
    {
        return [
            'called_at' => 'datetime',
            'started_at' => 'datetime',
            'billing_at' => 'datetime',
            'completed_at' => 'datetime',
            'skipped_at' => 'datetime',
        ];
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}
