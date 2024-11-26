<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalleAttente extends Model
{
    use HasFactory;
    protected $table = 'salle_attente'; // Nom de la table

    protected $fillable = [
        'patient_id',
        'entree',
        'sortie',
    ];

    /**
     * Relation avec le modèle Patient.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}
