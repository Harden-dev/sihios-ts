<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Librairie extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'file_path', 'mime_type','file_img', 'size', 'categorie_id'];

    protected $table = 'librairies';

    public function auteurs()
    {
        return $this->belongsToMany(Auteur::class, 'librairie_auteur');
    }

    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'categorie_id');
    }
}
