<?php

use App\Http\Controllers\CategoriaDespesaController;
use App\Http\Controllers\DespesaController;
use App\Http\Controllers\ReceitaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ReceitaController::class, 'index'])->name('financeiro.index');
Route::get('/movimentacoes', [ReceitaController::class, 'movimentacoes'])->name('financeiro.movimentacoes');

Route::post('/receitas', [ReceitaController::class, 'store'])->name('receitas.store');
Route::put('/receitas/{receita}', [ReceitaController::class, 'update'])->name('receitas.update');
Route::delete('/receitas/{receita}', [ReceitaController::class, 'destroy'])->name('receitas.destroy');

Route::post('/despesas', [DespesaController::class, 'store'])->name('despesas.store');
Route::put('/despesas/{despesa}', [DespesaController::class, 'update'])->name('despesas.update');
Route::delete('/despesas/{despesa}', [DespesaController::class, 'destroy'])->name('despesas.destroy');
Route::patch('/despesas/{despesa}/pagar', [DespesaController::class, 'marcarComoPaga'])->name('despesas.pagar');
Route::post('/categorias-despesa', [CategoriaDespesaController::class, 'store'])->name('categorias-despesa.store');
Route::delete('/categorias-despesa/{categoriaDespesa}', [CategoriaDespesaController::class, 'destroy'])->name('categorias-despesa.destroy');
