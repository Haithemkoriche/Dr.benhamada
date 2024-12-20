<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = [
        'consultation_id',
        'patient_id',
        'montant',
        'versement',
        'reste',
        'motif',
        'date',
    ];
    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
