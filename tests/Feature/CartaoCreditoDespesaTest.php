<?php

namespace Tests\Feature;

use App\Models\CategoriaDespesa;
use App\Models\Despesa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartaoCreditoDespesaTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_associates_credit_card_expense_with_invoice_category(): void
    {
        $categoriaCompras = CategoriaDespesa::create(['nome' => 'Compras']);
        $categoriaFaturaInter = CategoriaDespesa::create(['nome' => 'Cartao de Credito - Inter']);

        $response = $this->post(route('despesas.store'), [
            'descricao' => 'Mercado no cartao',
            'categoria_despesa_id' => $categoriaCompras->id,
            'valor' => 250.90,
            'tipo' => 'variavel',
            'eh_cartao_credito' => '1',
            'cartao_credito_nome' => 'inter',
            'recorrente' => '0',
            'periodicidade' => null,
            'data_vencimento' => '2026-03-20',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('despesas', [
            'descricao' => 'Mercado no cartao',
            'eh_cartao_credito' => 1,
            'cartao_credito_nome' => 'inter',
            'cartao_fatura_categoria_id' => $categoriaFaturaInter->id,
        ]);
    }

    public function test_paying_invoice_category_marks_associated_credit_card_expenses_as_paid(): void
    {
        $categoriaCompras = CategoriaDespesa::create(['nome' => 'Compras']);
        $categoriaFaturaInter = CategoriaDespesa::create(['nome' => 'Cartao de Credito - Inter']);
        $categoriaFaturaSantander = CategoriaDespesa::create(['nome' => 'Cartao de Credito - Santander']);

        $despesaInter = Despesa::create([
            'descricao' => 'Farmacia Inter',
            'categoria_despesa_id' => $categoriaCompras->id,
            'valor' => 89.90,
            'tipo' => 'variavel',
            'eh_cartao_credito' => true,
            'cartao_credito_nome' => 'inter',
            'cartao_fatura_categoria_id' => $categoriaFaturaInter->id,
            'recorrente' => false,
            'periodicidade' => null,
            'recorrencia_uid' => null,
            'data_vencimento' => '2026-03-10',
            'pago' => false,
            'pago_cartao_credito' => false,
            'forma_pagamento' => null,
        ]);

        $despesaSantander = Despesa::create([
            'descricao' => 'Supermercado Santander',
            'categoria_despesa_id' => $categoriaCompras->id,
            'valor' => 120.00,
            'tipo' => 'variavel',
            'eh_cartao_credito' => true,
            'cartao_credito_nome' => 'santander',
            'cartao_fatura_categoria_id' => $categoriaFaturaSantander->id,
            'recorrente' => false,
            'periodicidade' => null,
            'recorrencia_uid' => null,
            'data_vencimento' => '2026-03-11',
            'pago' => false,
            'pago_cartao_credito' => false,
            'forma_pagamento' => null,
        ]);

        $faturaInter = Despesa::create([
            'descricao' => 'Fatura Inter',
            'categoria_despesa_id' => $categoriaFaturaInter->id,
            'valor' => 209.90,
            'tipo' => 'fixa',
            'eh_cartao_credito' => false,
            'cartao_credito_nome' => null,
            'cartao_fatura_categoria_id' => null,
            'recorrente' => false,
            'periodicidade' => null,
            'recorrencia_uid' => null,
            'data_vencimento' => '2026-03-25',
            'pago' => false,
            'pago_cartao_credito' => false,
            'forma_pagamento' => null,
        ]);

        $this->patchJson(route('despesas.pagar', $faturaInter), [
            'forma_pagamento' => 'pix',
        ])->assertOk();

        $this->assertDatabaseHas('despesas', [
            'id' => $despesaInter->id,
            'pago' => 1,
            'pago_cartao_credito' => 1,
            'forma_pagamento' => 'cartao_credito',
        ]);

        $this->assertDatabaseHas('despesas', [
            'id' => $despesaSantander->id,
            'pago' => 0,
        ]);
    }
}
