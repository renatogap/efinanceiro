<?php

namespace Tests\Feature;

use App\Models\CategoriaDespesa;
use App\Models\Despesa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DespesaDestroyTest extends TestCase
{
    use RefreshDatabase;

    public function test_destroy_deletes_only_selected_expense_by_default(): void
    {
        $categoria = CategoriaDespesa::create(['nome' => 'Moradia '.Str::random(8)]);
        $recorrenciaUid = (string) Str::uuid();

        $despesaAtual = Despesa::create([
            'descricao' => 'Aluguel Marco',
            'categoria_despesa_id' => $categoria->id,
            'valor' => 1200,
            'tipo' => 'fixa',
            'recorrente' => true,
            'periodicidade' => 'mensal',
            'recorrencia_uid' => $recorrenciaUid,
            'data_vencimento' => '2026-03-10',
            'pago' => false,
            'pago_cartao_credito' => false,
            'forma_pagamento' => null,
        ]);

        $despesaFutura = Despesa::create([
            'descricao' => 'Aluguel Abril',
            'categoria_despesa_id' => $categoria->id,
            'valor' => 1200,
            'tipo' => 'fixa',
            'recorrente' => true,
            'periodicidade' => 'mensal',
            'recorrencia_uid' => $recorrenciaUid,
            'data_vencimento' => '2026-04-10',
            'pago' => false,
            'pago_cartao_credito' => false,
            'forma_pagamento' => null,
        ]);

        $this->deleteJson(route('despesas.destroy', $despesaAtual));

        $this->assertDatabaseMissing('despesas', ['id' => $despesaAtual->id]);
        $this->assertDatabaseHas('despesas', ['id' => $despesaFutura->id]);
    }

    public function test_destroy_can_delete_current_and_future_recurring_expenses(): void
    {
        $categoria = CategoriaDespesa::create(['nome' => 'Moradia '.Str::random(8)]);
        $recorrenciaUid = (string) Str::uuid();

        $despesaPassada = Despesa::create([
            'descricao' => 'Aluguel Fevereiro',
            'categoria_despesa_id' => $categoria->id,
            'valor' => 1200,
            'tipo' => 'fixa',
            'recorrente' => true,
            'periodicidade' => 'mensal',
            'recorrencia_uid' => $recorrenciaUid,
            'data_vencimento' => '2026-02-10',
            'pago' => false,
            'pago_cartao_credito' => false,
            'forma_pagamento' => null,
        ]);

        $despesaAtual = Despesa::create([
            'descricao' => 'Aluguel Marco',
            'categoria_despesa_id' => $categoria->id,
            'valor' => 1200,
            'tipo' => 'fixa',
            'recorrente' => true,
            'periodicidade' => 'mensal',
            'recorrencia_uid' => $recorrenciaUid,
            'data_vencimento' => '2026-03-10',
            'pago' => false,
            'pago_cartao_credito' => false,
            'forma_pagamento' => null,
        ]);

        $despesaFutura = Despesa::create([
            'descricao' => 'Aluguel Abril',
            'categoria_despesa_id' => $categoria->id,
            'valor' => 1200,
            'tipo' => 'fixa',
            'recorrente' => true,
            'periodicidade' => 'mensal',
            'recorrencia_uid' => $recorrenciaUid,
            'data_vencimento' => '2026-04-10',
            'pago' => false,
            'pago_cartao_credito' => false,
            'forma_pagamento' => null,
        ]);

        $this->deleteJson(route('despesas.destroy', $despesaAtual), [
            '_excluir_futuras' => '1',
        ]);

        $this->assertDatabaseHas('despesas', ['id' => $despesaPassada->id]);
        $this->assertDatabaseMissing('despesas', ['id' => $despesaAtual->id]);
        $this->assertDatabaseMissing('despesas', ['id' => $despesaFutura->id]);
    }
}