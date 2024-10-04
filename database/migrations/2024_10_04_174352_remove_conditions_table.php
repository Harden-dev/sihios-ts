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
        //
         // Suppression de la table pivot condition_parcours
         Schema::dropIfExists('condition_parcour');

         // Suppression de la table conditions
         Schema::dropIfExists('conditions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
