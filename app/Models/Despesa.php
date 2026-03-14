<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Despesa extends Model
{
    protected $fillable = [
        'descricao',
        'categoria_despesa_id',
        'valor',
        'tipo',
        'recorrente',
        'periodicidade',
        'recorrencia_uid',
        'data_vencimento',
        'pago',
        'pago_cartao_credito',
        'forma_pagamento',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'recorrente' => 'boolean',
        'data_vencimento' => 'date',
        'pago' => 'boolean',
        'pago_cartao_credito' => 'boolean',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaDespesa::class, 'categoria_despesa_id');
    }
}
