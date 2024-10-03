<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Condition extends Model
{
    use HasFactory;

    protected $fillable =
    [
        'label'
    ];

    public function parcours()
    {
        return $this->belongsToMany(Parcour::class, 'condition_parcour');
    }
}
