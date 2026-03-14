<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias_despesa', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 60)->unique();
            $table->timestamps();
        });

        $agora = now();

        DB::table('categorias_despesa')->insert([
            ['nome' => 'Alimentacao', 'created_at' => $agora, 'updated_at' => $agora],
            ['nome' => 'Assinaturas', 'created_at' => $agora, 'updated_at' => $agora],
            ['nome' => 'Doacao', 'created_at' => $agora, 'updated_at' => $agora],
            ['nome' => 'Educacao', 'created_at' => $agora, 'updated_at' => $agora],
            ['nome' => 'Entretenimento', 'created_at' => $agora, 'updated_at' => $agora],
            ['nome' => 'Financeiro', 'created_at' => $agora, 'updated_at' => $agora],
            ['nome' => 'Investimento', 'created_at' => $agora, 'updated_at' => $agora],
            ['nome' => 'Lazer', 'created_at' => $agora, 'updated_at' => $agora],
            ['nome' => 'Moradia', 'created_at' => $agora, 'updated_at' => $agora],
            ['nome' => 'Saude', 'created_at' => $agora, 'updated_at' => $agora],
            ['nome' => 'Transporte', 'created_at' => $agora, 'updated_at' => $agora],
            ['nome' => 'Despesas Filhas', 'created_at' => $agora, 'updated_at' => $agora],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias_despesa');
    }
};