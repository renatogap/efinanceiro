<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaDespesa extends Model
{
    protected $table = 'categorias_despesa';

    protected $fillable = [
        'nome',
    ];

    public function despesas(): HasMany
    {
        return $this->hasMany(Despesa::class);
    }
}