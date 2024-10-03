<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parcour extends Model
{
    use HasFactory;

    protected $fillable =
    [
        'label',
        'field',
        'description',
        'file_path',
        'mime_type',
        'size'
    ];

    public function conditions()
    {
        return $this->belongsToMany(Condition::class, 'condition_parcour');
    }
}
