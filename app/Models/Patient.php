<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'prenom',
        'nom_de_famille',
        'sexe',
        'groupe_sanguin',
        'adresse',
        'commune',
        'date_naissance',
        'telephone',
        'contact_urgence',
        'antecedents_medicaux',
        'antecedents_chirurgicaux',
        'antecedents_familiales',
        'allergies_connues',
    ];
}
