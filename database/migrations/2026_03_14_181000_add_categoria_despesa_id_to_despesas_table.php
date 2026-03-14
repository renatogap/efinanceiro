<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('despesas', function (Blueprint $table) {
            $table->foreignId('categoria_despesa_id')->nullable()->after('descricao')->constrained('categorias_despesa');
        });

        $categoriaFinanceiroId = DB::table('categorias_despesa')->where('nome', 'Financeiro')->value('id');

        if ($categoriaFinanceiroId) {
            DB::table('despesas')
                ->whereNull('categoria_despesa_id')
                ->update(['categoria_despesa_id' => $categoriaFinanceiroId]);
        }

        Schema::table('despesas', function (Blueprint $table) {
            $table->foreignId('categoria_despesa_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('despesas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('categoria_despesa_id');
        });
    }
};