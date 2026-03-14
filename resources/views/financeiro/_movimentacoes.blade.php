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
                <h3 style="margin: 0 0 10px; font-size: .92rem; color: #047857;">Receitas do periodo</h3>
                <div class="list">
                    @forelse ($receitas as $receita)
                        <article class="item" data-item>
                            <div class="item-top">
                                <div>
                                    <strong>{{ $receita->descricao }}</strong>
                                    <small>{{ $receita->data_credito->format('d/m/Y') }} · {{ $receita->fonte ?: 'Sem fonte' }}</small>
                                </div>
                                <div class="amount income">+ R$ {{ number_format($receita->valor, 2, ',', '.') }}</div>
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
                                <form action="{{ route('receitas.destroy', $receita) }}" method="POST" data-ajax-delete>
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
                <h3 style="margin: 0 0 10px; font-size: .92rem; color: #b91c1c;">Despesas do periodo</h3>
                <div class="list">
                    @forelse ($despesas as $despesa)
                        <article class="item" data-item>
                            <div class="item-top">
                                <div>
                                    <strong>{{ $despesa->descricao }}</strong>
                                    <small>
                                        {{ $despesa->data_vencimento->format('d/m/Y') }} · {{ ucfirst($despesa->tipo) }}
                                        @if ($despesa->recorrente && $despesa->periodicidade)
                                            · {{ ucfirst($despesa->periodicidade) }}
                                            <span class="material-icons-round recurrence-icon" title="Despesa recorrente">autorenew</span>
                                        @endif
                                        @if ($despesa->pago)
                                            <span class="paid-badge"><span class="material-icons-round">task_alt</span>Pago</span>
                                        @endif
                                    </small>
                                </div>
                                <div class="amount expense">- R$ {{ number_format($despesa->valor, 2, ',', '.') }}</div>
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
                                    data-valor="{{ number_format((float) $despesa->valor, 2, '.', '') }}"
                                    data-tipo="{{ $despesa->tipo }}"
                                    data-recorrente="{{ $despesa->recorrente ? '1' : '0' }}"
                                    data-periodicidade="{{ $despesa->periodicidade }}"
                                    data-data="{{ $despesa->data_vencimento->format('Y-m-d') }}"
                                >
                                    <span class="material-icons-round">edit</span>
                                </button>
                                <form action="{{ route('despesas.destroy', $despesa) }}" method="POST" data-ajax-delete>
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
                data-periodo="{{ $periodoLabel }}"
            ></canvas>
        </div>
        <div class="chart-summary">
            <div class="chart-pill income">Receitas: R$ {{ number_format($totalReceitas, 2, ',', '.') }}</div>
            <div class="chart-pill expense">Despesas: R$ {{ number_format($totalDespesas, 2, ',', '.') }}</div>
        </div>
    </div>
</article>
