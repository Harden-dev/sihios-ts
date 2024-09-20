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
        Schema::create('librairie_auteur', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('librairie_id');
            $table->unsignedBigInteger('auteur_id');

            $table->foreign('librairie_id')->references('id')->on('librairies');
            $table->foreign('auteur_id')->references('id')->on('auteurs');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('librairie_auteur');
    }
};
