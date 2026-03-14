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
        Schema::table('despesas', function (Blueprint $table) {
            $table->boolean('eh_cartao_credito')->default(false);
            $table->string('cartao_credito_nome', 30)->nullable();
            $table->foreignId('cartao_fatura_categoria_id')
                ->nullable()
                ->constrained('categorias_despesa')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('despesas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cartao_fatura_categoria_id');
            $table->dropColumn('cartao_credito_nome');
            $table->dropColumn('eh_cartao_credito');
        });
    }
};
