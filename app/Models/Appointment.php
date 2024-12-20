<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id', 'start', 'end', 'description'
    ];

   

    // Définir la relation avec le modèle Patient
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
