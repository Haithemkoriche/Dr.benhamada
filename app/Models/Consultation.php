<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'utilisateur_id',
        'date_consultation',
        'heure_consultation',
        'motif',
        'diagnostic',
        'traitement',
    ];

    // Relation avec le modèle Patient
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    // Relation avec le modèle Utilisateur
    public function utilisateur()
    {
        return $this->belongsTo(User::class);
    }
}
