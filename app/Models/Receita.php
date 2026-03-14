<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receita extends Model
{
    protected $fillable = [
        'descricao',
        'valor',
        'data_credito',
        'fonte',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_credito' => 'date',
    ];
}
