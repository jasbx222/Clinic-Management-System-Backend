<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientJourneyEvent extends Model
{
    protected $fillable = [
        'appointment_id',
        'patient_id',
        'event_type',
        'note',
        'created_by',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
