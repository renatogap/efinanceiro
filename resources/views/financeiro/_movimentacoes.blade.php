<article class="card card-wide">
    <h2 class="card-title">
        <span class="material-icons-round">view_timeline</span>
        Movimentacoes
        <span class="paid-percent-badge">{{ number_format($percentualDespesasPagas, 1, ',', '.') }}% pagas</span>
    </h2>
    <div class="tabs" role="tablist" aria-label="Tipo de visualizacao">
        <button class="tab-btn active" type="button" data-tab-target="tab-lista">Lista</button>
        <button class="tab-btn" type="button" data-tab-target="tab-grafico">Grafico</button>
    </div>

    <div id="tab-lista" class="tab-panel active">
        <div class="layout" style="margin-top: 0; gap: 12px;">
            <div>
                <div class="section-title-row">
                    <div class="section-title-main">
                        <h3 style="color: #047857;">Receitas do periodo</h3>
                    </div>
                    <button
                        class="icon-btn inline-add-btn income-add"
                        type="button"
                        data-open-inline-modal="modalReceita"
                        aria-label="Cadastrar nova receita"
                    >
                        <span class="material-icons-round">add_circle</span>
                    </button>
                </div>
                <div class="list">
                    @forelse ($receitas as $receita)
                        <article class="item" data-item>
                            <div class="item-top">
                                <div>
                                    <strong>{{ $receita->descricao }}</strong>
                                    <small>{{ $receita->data_credito->format('d/m/Y') }} · {{ $receita->fonte ?: 'Sem fonte' }}</small>
                                </div>
                                <div class="amount income receita-amount" data-visible-value="+ R$ {{ number_format($receita->valor, 2, ',', '.') }}">+ R$ {{ number_format($receita->valor, 2, ',', '.') }}</div>
                            </div>
                            <div class="item-actions">
                                <button
                                    class="icon-btn"
                                    type="button"
                                    aria-label="Editar receita"
                                    data-edit-receita
                                    data-id="{{ $receita->id }}"
                                    data-descricao="{{ $receita->descricao }}"
                                    data-valor="{{ number_format((float) $receita->valor, 2, '.', '') }}"
                                    data-data="{{ $receita->data_credito->format('Y-m-d') }}"
                                    data-fonte="{{ $receita->fonte }}"
                                >
                                    <span class="material-icons-round">edit</span>
                                </button>
                                <form action="{{ route('receitas.destroy', $receita) }}" method="POST" data-ajax-delete-receita>
                                    @csrf
                                    @method('DELETE')
                                    <button class="icon-btn danger" type="submit" aria-label="Excluir receita">
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </form>
                            </div>
                        </article>
                    @empty
                        <div class="empty">Nenhuma receita cadastrada para este periodo.</div>
                    @endforelse
                </div>
            </div>

            <div>
                <div class="section-title-row">
                    <div class="section-title-main">
                        <h3 style="color: #b91c1c;">Despesas do periodo</h3>
                    </div>
                    <button
                        class="icon-btn inline-add-btn expense-add"
                        type="button"
                        data-open-inline-modal="modalDespesa"
                        aria-label="Cadastrar nova despesa"
                    >
                        <span class="material-icons-round">add_circle</span>
                    </button>
                </div>
                <div class="list">
                    @php
                        $hoje = now()->startOfDay();
                        $despesasOrdenadas = $despesas->sortBy(function ($despesa) use ($hoje) {
                            $vencida = ! $despesa->pago && $despesa->data_vencimento->lt($hoje);
                            return sprintf('%d|%s', $vencida ? 0 : 1, mb_strtolower($despesa->descricao ?? ''));
                        }, SORT_NATURAL);
                    @endphp
                    @forelse ($despesasOrdenadas as $despesa)
                        @php
                            $despesaVencida = ! $despesa->pago && $despesa->data_vencimento->lt($hoje);
                        @endphp
                        <article class="item{{ $despesaVencida ? ' item-overdue' : '' }}" data-item>
                            <div class="item-top">
                                <div>
                                    <strong>{{ $despesa->descricao }}</strong>
                                    @php
                                        $iconeFormaPagamento = match ($despesa->forma_pagamento) {
                                            'cartao_credito' => ['icone' => 'credit_card', 'titulo' => 'Pago em cartao de credito', 'rotulo' => 'Cartao de Credito'],
                                            'cartao_debito' => ['icone' => 'payments', 'titulo' => 'Pago em cartao de debito', 'rotulo' => 'Cartao de Debito'],
                                            'pix' => ['icone' => 'qr_code_2', 'titulo' => 'Pago via Pix', 'rotulo' => 'Pix'],
                                            'dinheiro' => ['icone' => 'attach_money', 'titulo' => 'Pago em dinheiro', 'rotulo' => 'Dinheiro'],
                                            'boleto' => ['icone' => 'receipt_long', 'titulo' => 'Pago via boleto', 'rotulo' => 'Boleto'],
                                            'transferencia' => ['icone' => 'swap_horiz', 'titulo' => 'Pago via transferencia', 'rotulo' => 'Transferencia'],
                                            default => ['icone' => 'task_alt', 'titulo' => 'Pago', 'rotulo' => 'Pago'],
                                        };
                                    @endphp
                                    <div class="item-meta">
                                        <small>
                                            {{ $despesa->data_vencimento->format('d/m/Y') }} · {{ $despesa->categoria?->nome ?? 'Sem categoria' }}
                                            @if ($despesa->recorrente)
                                                <span class="material-icons-round recurrence-icon" title="Despesa recorrente">autorenew</span>
                                            @endif
                                            @if ($despesa->eh_cartao_credito)
                                                · Cartao {{ ucfirst($despesa->cartao_credito_nome ?? '') }}
                                            @endif
                                        </small>
                                        @if ($despesa->pago || $despesaVencida)
                                            <div class="item-status-badges">
                                                @if ($despesa->pago)
                                                    <button
                                                        class="paid-badge"
                                                        type="button"
                                                        title="{{ $iconeFormaPagamento['titulo'] }}"
                                                        data-edit-pagamento
                                                        data-id="{{ $despesa->id }}"
                                                        data-forma-pagamento="{{ $despesa->forma_pagamento }}"
                                                        aria-label="Editar forma de pagamento"
                                                    ><span class="material-icons-round">{{ $iconeFormaPagamento['icone'] }}</span>{{ $iconeFormaPagamento['rotulo'] }}</button>
                                                @endif
                                                @if ($despesaVencida)
                                                    <span class="overdue-badge" title="Despesa vencida">Vencida</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="amount expense despesa-amount{{ $despesa->eh_cartao_credito ? ' is-informative' : '' }}" data-visible-value="- R$ {{ number_format($despesa->valor, 2, ',', '.') }}">- R$ {{ number_format($despesa->valor, 2, ',', '.') }}</div>
                            </div>
                            <div class="item-actions">
                                @if (! $despesa->pago)
                                    <form action="{{ route('despesas.pagar', $despesa) }}" method="POST" data-ajax-pagar>
                                        @csrf
                                        @method('PATCH')
                                        <button class="icon-btn success" type="submit" aria-label="Marcar despesa como paga">
                                            <span class="material-icons-round">task_alt</span>
                                        </button>
                                    </form>
                                @endif
                                <button
                                    class="icon-btn"
                                    type="button"
                                    aria-label="Editar despesa"
                                    data-edit-despesa
                                    data-id="{{ $despesa->id }}"
                                    data-descricao="{{ $despesa->descricao }}"
                                    data-categoria="{{ $despesa->categoria_despesa_id }}"
                                    data-valor="{{ number_format((float) $despesa->valor, 2, '.', '') }}"
                                    data-tipo="{{ $despesa->tipo }}"
                                    data-eh-cartao-credito="{{ $despesa->eh_cartao_credito ? '1' : '0' }}"
                                    data-cartao-credito-nome="{{ $despesa->cartao_credito_nome }}"
                                    data-recorrente="{{ $despesa->recorrente ? '1' : '0' }}"
                                    data-periodicidade="{{ $despesa->periodicidade }}"
                                    data-data="{{ $despesa->data_vencimento->format('Y-m-d') }}"
                                >
                                    <span class="material-icons-round">edit</span>
                                </button>
                                <form
                                    action="{{ route('despesas.destroy', $despesa) }}"
                                    method="POST"
                                    data-ajax-delete-despesa
                                    data-recorrente="{{ $despesa->recorrente ? '1' : '0' }}"
                                    data-has-futuras="{{ ! empty($despesa->has_futuras_recorrencias) ? '1' : '0' }}"
                                    data-descricao="{{ $despesa->descricao }}"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button class="icon-btn danger" type="submit" aria-label="Excluir despesa">
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </form>
                            </div>
                        </article>
                    @empty
                        <div class="empty">Nenhuma despesa cadastrada para este periodo.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div id="tab-grafico" class="tab-panel">
        <div class="chart-wrap">
            <canvas
                id="finance-month-chart"
                data-receitas="{{ (float) $totalReceitas }}"
                data-despesas="{{ (float) $totalDespesas }}"
                data-despesas-por-categoria='@json($despesasPorCategoria)'
                data-periodo="{{ $periodoLabel }}"
            ></canvas>
        </div>
        <div class="chart-summary">
            <div class="chart-pill income">Receitas: R$ {{ number_format($totalReceitas, 2, ',', '.') }}</div>
            <div class="chart-pill expense">Despesas: R$ {{ number_format($totalDespesas, 2, ',', '.') }}</div>
        </div>
    </div>
</article>
