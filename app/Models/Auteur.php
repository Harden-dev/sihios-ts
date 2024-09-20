<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auteur extends Model
{
    use HasFactory;

    protected $fillable = 
    [
        'name'
    ];

    public function librairies()
    {
        return $this->belongsToMany(Librairie::class, 'librairie_auteur');
    }

}
