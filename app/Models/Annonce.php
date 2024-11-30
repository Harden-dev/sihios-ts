<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Annonce extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable =
    [
        'title',
        'file_path',
        'description',
        'label',
        'category'
    ];

    protected $casts = [
        'label' => 'array'
    ];

}
