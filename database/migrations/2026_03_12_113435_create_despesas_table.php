<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('despesas', function (Blueprint $table) {
            $table->id();
            $table->string('descricao', 120);
            $table->decimal('valor', 12, 2);
            $table->enum('tipo', ['fixa', 'variavel']);
            $table->boolean('recorrente')->default(false);
            $table->enum('periodicidade', ['semanal', 'mensal', 'anual'])->nullable();
            $table->date('data_vencimento');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('despesas');
    }
};
