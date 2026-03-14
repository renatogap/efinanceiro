<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FinanceiroSnapshotSeeder extends Seeder
{
    public function run(): void
    {
        $snapshotPath = database_path('seeders/data/financeiro_snapshot.json');

        if (! is_file($snapshotPath)) {
            throw new \RuntimeException('Arquivo de snapshot nao encontrado: '.$snapshotPath);
        }

        $snapshot = json_decode(file_get_contents($snapshotPath), true);

        if (! is_array($snapshot)) {
            throw new \RuntimeException('Snapshot financeiro invalido.');
        }

        $categorias = $snapshot['categorias'] ?? [];
        $receitas = $snapshot['receitas'] ?? [];
        $despesas = $snapshot['despesas'] ?? [];
        $hasPagoCartaoCredito = Schema::hasColumn('despesas', 'pago_cartao_credito');
        $hasFormaPagamento = Schema::hasColumn('despesas', 'forma_pagamento');

        Schema::disableForeignKeyConstraints();
        DB::table('despesas')->truncate();
        DB::table('receitas')->truncate();
        DB::table('categorias_despesa')->truncate();
        Schema::enableForeignKeyConstraints();

        DB::transaction(function () use ($categorias, $receitas, $despesas, $hasPagoCartaoCredito, $hasFormaPagamento) {

            if (! empty($categorias)) {
                DB::table('categorias_despesa')->insert(array_map(function (array $categoria) {
                    return [
                        'id' => $categoria['id'],
                        'nome' => $categoria['nome'],
                        'created_at' => $categoria['created_at'],
                        'updated_at' => $categoria['updated_at'],
                    ];
                }, $categorias));
            }

            if (! empty($receitas)) {
                DB::table('receitas')->insert(array_map(function (array $receita) {
                    return [
                        'descricao' => $receita['descricao'],
                        'valor' => $receita['valor'],
                        'data_credito' => $receita['data_credito'],
                        'fonte' => $receita['fonte'],
                        'created_at' => $receita['created_at'],
                        'updated_at' => $receita['updated_at'],
                    ];
                }, $receitas));
            }

            if (! empty($despesas)) {
                DB::table('despesas')->insert(array_map(function (array $despesa) use ($hasPagoCartaoCredito, $hasFormaPagamento) {
                    $registro = [
                        'descricao' => $despesa['descricao'],
                        'categoria_despesa_id' => $despesa['categoria_despesa_id'],
                        'valor' => $despesa['valor'],
                        'tipo' => $despesa['tipo'],
                        'recorrente' => $despesa['recorrente'],
                        'periodicidade' => $despesa['periodicidade'],
                        'recorrencia_uid' => $despesa['recorrencia_uid'],
                        'data_vencimento' => $despesa['data_vencimento'],
                        'pago' => $despesa['pago'],
                        'created_at' => $despesa['created_at'],
                        'updated_at' => $despesa['updated_at'],
                    ];

                    if ($hasPagoCartaoCredito) {
                        $registro['pago_cartao_credito'] = $despesa['pago_cartao_credito'] ?? false;
                    }

                    if ($hasFormaPagamento) {
                        $registro['forma_pagamento'] = $despesa['forma_pagamento'] ?? null;
                    }

                    return $registro;
                }, $despesas));
            }
        });
    }
}