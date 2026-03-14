<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Despesa extends Model
{
    protected $fillable = [
        'descricao',
        'valor',
        'tipo',
        'recorrente',
        'periodicidade',
        'recorrencia_uid',
        'data_vencimento',
        'pago',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'recorrente' => 'boolean',
        'data_vencimento' => 'date',
        'pago' => 'boolean',
    ];
}
