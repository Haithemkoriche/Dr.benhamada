<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('prenom');
            $table->string('nom_de_famille');
            $table->enum('sexe', ['Homme', 'Femme']);
            $table->string('groupe_sanguin')->nullable();
            $table->string('adresse')->nullable();
            $table->string('commune')->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('telephone')->nullable();
            $table->string('contact_urgence')->nullable();
            $table->text('antecedents_medicaux')->nullable();
            $table->text('antecedents_chirurgicaux')->nullable();
            $table->text('antecedents_familiales')->nullable();
            $table->text('allergies_connues')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
