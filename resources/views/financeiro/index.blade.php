<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>eFinanceiro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Manrope:wght@400;500;600;700&family=Material+Icons+Round" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/js/app.js'])
    @endif
    <style>
        :root {
            --bg-1: #f3f8ff;
            --bg-2: #effff7;
            --ink: #111827;
            --muted: #6b7280;
            --card: rgba(255, 255, 255, 0.9);
            --stroke: rgba(15, 23, 42, 0.09);
            --brand: #0f766e;
            --brand-dark: #0b5f59;
            --income: #047857;
            --expense: #b91c1c;
            --shadow: 0 14px 36px rgba(15, 23, 42, 0.1);
            --radius: 22px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Manrope', sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at 8% 8%, #d7fef4 0 24%, transparent 42%),
                radial-gradient(circle at 94% 20%, #dde8ff 0 22%, transparent 44%),
                linear-gradient(140deg, var(--bg-1), var(--bg-2));
            min-height: 100vh;
            padding: 18px 14px 34px;
        }

        .app {
            max-width: 980px;
            margin: 0 auto;
            animation: rise 420ms ease-out;
        }

        .header {
            background: linear-gradient(140deg, #00412d, #1f2937);
            color: #fff;
            border-radius: var(--radius);
            padding: 18px;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }

        .header::after {
            content: '';
            position: absolute;
            width: 180px;
            height: 180px;
            right: -40px;
            top: -70px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(45, 212, 191, 0.5), transparent 70%);
        }

        .header h1 {
            margin: 0;
            font-family: 'Sora', sans-serif;
            font-weight: 800;
            font-size: 1.5rem;
            letter-spacing: -0.03em;
        }

        .header p {
            margin: 8px 0 0;
            color: rgba(255, 255, 255, 0.82);
            font-size: 0.93rem;
        }

        .stats {
            margin-top: 14px;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .stat {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 10px;
            backdrop-filter: blur(6px);
        }

        .stat .label {
            font-size: 0.75rem;
            opacity: 0.82;
        }

        .stat .value {
            margin-top: 4px;
            font-weight: 700;
            font-size: 0.98rem;
        }

        .layout {
            margin-top: 14px;
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .card {
            border: 1px solid var(--stroke);
            border-radius: var(--radius);
            background: var(--card);
            backdrop-filter: blur(8px);
            box-shadow: var(--shadow);
            padding: 16px;
        }

        .card-title {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0 0 12px;
            font-family: 'Sora', sans-serif;
            font-weight: 700;
            font-size: 1.02rem;
        }

        .material-icons-round {
            font-size: 1.2rem;
        }

        .filter-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .arrow-btn {
            width: 42px;
            height: 42px;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            background: #fff;
            color: #0f172a;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
        }

        .month-input {
            flex: 1;
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            background: #fff;
            font: inherit;
            padding: 11px 12px;
            color: #111827;
            min-height: 42px;
        }

        .month-input:focus,
        input:focus,
        select:focus {
            outline: 2px solid rgba(15, 118, 110, 0.22);
            border-color: var(--brand);
        }

        .actions-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .primary-btn,
        .secondary-btn {
            border-radius: 14px;
            padding: 12px 14px;
            font: inherit;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
        }

        .primary-btn {
            border: none;
            color: #fff;
            background: linear-gradient(135deg, var(--brand), var(--brand-dark));
        }

        .secondary-btn {
            border: 1px solid #cbd5e1;
            color: white;
            background: #bd2e2e;
        }

        .flash {
            margin-top: 12px;
            border: 1px solid #99f6e4;
            background: #ecfeff;
            color: #0f766e;
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 0.88rem;
        }

        .errors {
            margin-top: 12px;
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #b91c1c;
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 0.86rem;
        }

        .errors ul {
            margin: 0;
            padding-left: 18px;
        }

        .list {
            display: grid;
            gap: 10px;
        }

        .tabs {
            display: inline-flex;
            gap: 8px;
            margin-bottom: 12px;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 12px;
        }

        .tab-btn {
            border: none;
            border-radius: 10px;
            padding: 8px 12px;
            font: inherit;
            font-weight: 700;
            background: transparent;
            color: #475569;
            cursor: pointer;
        }

        .tab-btn.active {
            background: #fff;
            color: #0f172a;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.1);
        }

        .tab-panel {
            display: none;
        }

        .tab-panel.active {
            display: block;
        }

        .chart-wrap {
            height: 280px;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #fff;
            padding: 14px;
        }

        .chart-summary {
            margin-top: 10px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .chart-pill {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #fff;
            padding: 8px 10px;
            font-size: 0.86rem;
            font-weight: 700;
        }

        .chart-pill.income {
            color: #15803d;
            border-color: #bbf7d0;
        }

        .chart-pill.expense {
            color: #b91c1c;
            border-color: #fecaca;
        }

        .item {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: #fff;
            padding: 10px;
            display: grid;
            gap: 9px;
            cursor: pointer;
            transition: border-color 0.2s ease;
        }

        .item:hover {
            border-color: #94a3b8;
        }

        .item-top {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 8px;
            align-items: center;
        }

        .item strong {
            font-size: 0.93rem;
        }

        .item small {
            display: block;
            color: var(--muted);
            margin-top: 2px;
            font-size: 0.76rem;
        }

        .amount {
            font-weight: 800;
            font-family: 'Sora', sans-serif;
            font-size: 0.9rem;
        }

        .income {
            color: var(--income);
        }

        .expense {
            color: var(--expense);
        }

        .item-actions {
            display: none;
            gap: 8px;
            justify-content: flex-end;
        }

        .item.open .item-actions {
            display: flex;
        }

        .icon-btn {
            width: 38px;
            height: 38px;
            border-radius: 11px;
            border: 1px solid #d1d5db;
            background: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #0f172a;
            padding: 0;
        }

        .icon-btn.danger {
            color: #b91c1c;
            border-color: #fecaca;
            background: #fff5f5;
        }

        .icon-btn.success {
            color: #166534;
            border-color: #bbf7d0;
            background: #f0fdf4;
        }

        .paid-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-left: 6px;
            padding: 2px 8px;
            border-radius: 999px;
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
            font-size: 0.7rem;
            font-weight: 700;
            vertical-align: middle;
        }

        .paid-badge .material-icons-round {
            font-size: 0.9rem;
        }

        .recurrence-icon {
            font-size: 0.95rem;
            color: #0f766e;
            vertical-align: middle;
            margin-left: 4px;
        }

        .paid-percent-badge {
            margin-left: auto;
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 999px;
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
            color: #166534;
            font-size: 0.74rem;
            font-weight: 700;
        }

        .empty {
            border: 1px dashed #d1d5db;
            border-radius: 12px;
            padding: 14px;
            text-align: center;
            color: var(--muted);
            font-size: 0.88rem;
        }

        .modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(2, 6, 23, 0.56);
            padding: 18px 14px;
            z-index: 40;
            align-items: center;
            justify-content: center;
        }

        .modal-backdrop.open {
            display: flex;
        }

        .modal {
            width: 100%;
            max-width: 480px;
            max-height: 92vh;
            overflow: auto;
            background: #fff;
            border-radius: 18px;
            border: 1px solid #e2e8f0;
            box-shadow: var(--shadow);
            padding: 16px;
        }

        .modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .modal-head h3 {
            margin: 0;
            font-family: 'Sora', sans-serif;
            font-size: 1rem;
        }

        form {
            display: grid;
            gap: 10px;
        }

        .field {
            display: grid;
            gap: 6px;
        }

        label {
            font-size: 0.83rem;
            font-weight: 700;
            color: #1f2937;
        }

        input,
        select {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            background: #fff;
            font: inherit;
            padding: 11px 12px;
            color: #111827;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .toggle {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.88rem;
            color: #334155;
        }

        .toggle input {
            width: 17px;
            height: 17px;
            accent-color: var(--brand);
        }

        @media (min-width: 920px) {
            body {
                padding: 26px;
            }

            .layout {
                grid-template-columns: 1fr 1fr;
            }

            .card-wide {
                grid-column: span 2;
            }
        }

        @keyframes rise {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    @php
    $periodoSelecionado = sprintf('%04d-%02d', $anoSelecionado, $mesSelecionado);
    $periodoLabel = sprintf('%02d/%04d', $mesSelecionado, $anoSelecionado);
    $openReceitaModal = $errors->any() && old('_origin') === 'create_receita';
    $openDespesaModal = $errors->any() && old('_origin') === 'create_despesa';
    @endphp

    <div class="app" data-open-receita="{{ $openReceitaModal ? '1' : '0' }}" data-open-despesa="{{ $openDespesaModal ? '1' : '0' }}">
        <section class="header">
            <h1>eFinanceiro</h1>
            <p>Controle suas finanças na palma da mão.</p>
            <div class="stats">
                <div class="stat">
                    <div class="label">Receitas</div>
                    <div class="value" id="stat-receitas">R$ {{ number_format($totalReceitas, 2, ',', '.') }}</div>
                </div>
                <div class="stat">
                    <div class="label">Despesas</div>
                    <div class="value" id="stat-despesas">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</div>
                </div>
                <div class="stat">
                    <div class="label">Saldo</div>
                    <div class="value" id="stat-saldo">R$ {{ number_format($saldo, 2, ',', '.') }}</div>
                </div>
            </div>
        </section>

        @if (session('status'))
        <div class="flash">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
        <div class="errors">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <section class="layout">
            <article class="card card-wide">
                <h2 class="card-title">
                    <span class="material-icons-round">tune</span>
                    Filtro por mes e ano
                </h2>
                <form id="formFiltro" method="GET" action="{{ route('financeiro.index') }}" style="display:block;">
                    <input type="hidden" name="mes" id="mesCampo" value="{{ $mesSelecionado }}">
                    <input type="hidden" name="ano" id="anoCampo" value="{{ $anoSelecionado }}">
                    <div class="filter-row">
                        <button class="arrow-btn" type="button" id="prevMonth" aria-label="Mes anterior">
                            <span class="material-icons-round">chevron_left</span>
                        </button>
                        <input class="month-input" type="month" id="periodoInput" value="{{ $periodoSelecionado }}">
                        <button class="arrow-btn" type="button" id="nextMonth" aria-label="Proximo mes">
                            <span class="material-icons-round">chevron_right</span>
                        </button>
                    </div>
                </form>
            </article>

            <article class="card card-wide">
                <h2 class="card-title">
                    <span class="material-icons-round">add_circle</span>
                    Cadastros
                </h2>
                <div class="actions-row">
                    <button class="primary-btn" type="button" data-open-modal="modalReceita">
                        <span class="material-icons-round">south_west</span>
                        Cadastrar Receita
                    </button>
                    <button class="secondary-btn" type="button" data-open-modal="modalDespesa">
                        <span class="material-icons-round">north_east</span>
                        Cadastrar Despesa
                    </button>
                </div>
            </article>

            <div id="movimentacoesContainer">
                @include('financeiro._movimentacoes')
            </div>
        </section>
    </div>

    <div class="modal-backdrop" id="modalReceita">
        <div class="modal">
            <div class="modal-head">
                <h3>Nova Receita</h3>
                <button class="icon-btn" type="button" data-close-modal="modalReceita" aria-label="Fechar">
                    <span class="material-icons-round">close</span>
                </button>
            </div>
            <form action="{{ route('receitas.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_origin" value="create_receita">
                <div class="field">
                    <label for="r_descricao">Descricao</label>
                    <input id="r_descricao" name="descricao" type="text" value="{{ old('descricao') }}" required>
                </div>
                <div class="row">
                    <div class="field">
                        <label for="r_valor">Valor</label>
                        <input id="r_valor" name="valor" type="number" step="0.01" min="0.01" value="{{ old('valor') }}" required>
                    </div>
                    <div class="field">
                        <label for="r_data_credito">Data do credito</label>
                        <input id="r_data_credito" name="data_credito" type="date" value="{{ old('data_credito') }}" required>
                    </div>
                </div>
                <div class="field">
                    <label for="r_fonte">Fonte</label>
                    <input id="r_fonte" name="fonte" type="text" value="{{ old('fonte') }}">
                </div>
                <button class="primary-btn" type="submit">
                    <span class="material-icons-round">save</span>
                    Salvar
                </button>
            </form>
        </div>
    </div>

    <div class="modal-backdrop" id="modalDespesa">
        <div class="modal">
            <div class="modal-head">
                <h3>Nova Despesa</h3>
                <button class="icon-btn" type="button" data-close-modal="modalDespesa" aria-label="Fechar">
                    <span class="material-icons-round">close</span>
                </button>
            </div>
            <form action="{{ route('despesas.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_origin" value="create_despesa">
                <div class="field">
                    <label for="d_descricao">Descricao</label>
                    <input id="d_descricao" name="descricao" type="text" required>
                </div>
                <div class="row">
                    <div class="field">
                        <label for="d_valor">Valor</label>
                        <input id="d_valor" name="valor" type="number" step="0.01" min="0.01" required>
                    </div>
                    <div class="field">
                        <label for="d_tipo">Tipo</label>
                        <select id="d_tipo" name="tipo" required>
                            <option value="fixa">Fixa</option>
                            <option value="variavel">Variavel</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="field">
                        <label for="d_data_vencimento">Data da despesa</label>
                        <input id="d_data_vencimento" name="data_vencimento" type="date" required>
                    </div>
                    <div class="field">
                        <label for="d_periodicidade">Periodicidade</label>
                        <select id="d_periodicidade" name="periodicidade">
                            <option value="">Somente uma vez</option>
                            <option value="semanal">Semanal</option>
                            <option value="mensal">Mensal</option>
                            <option value="anual">Anual</option>
                        </select>
                    </div>
                </div>
                <label class="toggle" for="d_recorrente">
                    <input id="d_recorrente" name="recorrente" type="checkbox" value="1">
                    Esta despesa e recorrente
                </label>
                <button class="primary-btn" type="submit">
                    <span class="material-icons-round">save</span>
                    Salvar
                </button>
            </form>
        </div>
    </div>

    <div class="modal-backdrop" id="modalEditReceita">
        <div class="modal">
            <div class="modal-head">
                <h3>Editar Receita</h3>
                <button class="icon-btn" type="button" data-close-modal="modalEditReceita" aria-label="Fechar">
                    <span class="material-icons-round">close</span>
                </button>
            </div>
            <form id="formEditReceita" method="POST">
                @csrf
                @method('PUT')
                <div class="field">
                    <label for="er_descricao">Descricao</label>
                    <input id="er_descricao" name="descricao" type="text" required>
                </div>
                <div class="row">
                    <div class="field">
                        <label for="er_valor">Valor</label>
                        <input id="er_valor" name="valor" type="number" step="0.01" min="0.01" required>
                    </div>
                    <div class="field">
                        <label for="er_data_credito">Data do credito</label>
                        <input id="er_data_credito" name="data_credito" type="date" required>
                    </div>
                </div>
                <div class="field">
                    <label for="er_fonte">Fonte</label>
                    <input id="er_fonte" name="fonte" type="text">
                </div>
                <button class="primary-btn" type="submit">
                    <span class="material-icons-round">save</span>
                    Atualizar
                </button>
            </form>
        </div>
    </div>

    <div class="modal-backdrop" id="modalEditDespesa">
        <div class="modal">
            <div class="modal-head">
                <h3>Editar Despesa</h3>
                <button class="icon-btn" type="button" data-close-modal="modalEditDespesa" aria-label="Fechar">
                    <span class="material-icons-round">close</span>
                </button>
            </div>
            <p style="margin: 0 0 10px; font-size: 0.78rem; color: #475569;">
                Em despesas fixas mensais recorrentes, a alteracao de valor vale apenas para os proximos meses.
            </p>
            <form id="formEditDespesa" method="POST">
                @csrf
                @method('PUT')
                <div class="field">
                    <label for="ed_descricao">Descricao</label>
                    <input id="ed_descricao" name="descricao" type="text" required>
                </div>
                <div class="row">
                    <div class="field">
                        <label for="ed_valor">Valor</label>
                        <input id="ed_valor" name="valor" type="number" step="0.01" min="0.01" required>
                    </div>
                    <div class="field">
                        <label for="ed_tipo">Tipo</label>
                        <select id="ed_tipo" name="tipo" required>
                            <option value="fixa">Fixa</option>
                            <option value="variavel">Variavel</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="field">
                        <label for="ed_data_vencimento">Data da despesa</label>
                        <input id="ed_data_vencimento" name="data_vencimento" type="date" required>
                    </div>
                    <div class="field">
                        <label for="ed_periodicidade">Periodicidade</label>
                        <select id="ed_periodicidade" name="periodicidade">
                            <option value="">Somente uma vez</option>
                            <option value="semanal">Semanal</option>
                            <option value="mensal">Mensal</option>
                            <option value="anual">Anual</option>
                        </select>
                    </div>
                </div>
                <label class="toggle" for="ed_recorrente">
                    <input id="ed_recorrente" name="recorrente" type="checkbox" value="1">
                    Esta despesa e recorrente
                </label>
                <button class="primary-btn" type="submit">
                    <span class="material-icons-round">save</span>
                    Atualizar
                </button>
            </form>
        </div>
    </div>

    <script>
        const formFiltro = document.getElementById('formFiltro');
        const periodoInput = document.getElementById('periodoInput');
        const mesCampo = document.getElementById('mesCampo');
        const anoCampo = document.getElementById('anoCampo');
        const movimentacoesContainer = document.getElementById('movimentacoesContainer');
        const movimentacoesUrl = "{{ route('financeiro.movimentacoes') }}";
        const formEditReceita = document.getElementById('formEditReceita');
        const formEditDespesa = document.getElementById('formEditDespesa');

        function formatCurrency(valor) {
            return Number(valor || 0).toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL',
            });
        }

        async function enviarFormularioAjax(form) {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(form),
            });

            if (!response.ok) {
                throw new Error('Falha na requisicao');
            }
        }

        function bindMovimentacoesEvents() {
            movimentacoesContainer.querySelectorAll('.tab-btn').forEach((button) => {
                button.addEventListener('click', () => {
                    const targetId = button.dataset.tabTarget;

                    movimentacoesContainer.querySelectorAll('.tab-btn').forEach((btn) => {
                        btn.classList.toggle('active', btn === button);
                    });

                    movimentacoesContainer.querySelectorAll('.tab-panel').forEach((panel) => {
                        panel.classList.toggle('active', panel.id === targetId);
                    });
                });
            });

            movimentacoesContainer.querySelectorAll('[data-item]').forEach((item) => {
                item.addEventListener('click', (event) => {
                    if (event.target.closest('button') || event.target.closest('form')) return;
                    item.classList.toggle('open');
                });
            });

            movimentacoesContainer.querySelectorAll('[data-edit-receita]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    formEditReceita.action = `/receitas/${btn.dataset.id}`;
                    document.getElementById('er_descricao').value = btn.dataset.descricao;
                    document.getElementById('er_valor').value = btn.dataset.valor;
                    document.getElementById('er_data_credito').value = btn.dataset.data;
                    document.getElementById('er_fonte').value = btn.dataset.fonte || '';
                    document.getElementById('modalEditReceita').classList.add('open');
                });
            });

            movimentacoesContainer.querySelectorAll('[data-edit-despesa]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    formEditDespesa.action = `/despesas/${btn.dataset.id}`;
                    document.getElementById('ed_descricao').value = btn.dataset.descricao;
                    document.getElementById('ed_valor').value = btn.dataset.valor;
                    document.getElementById('ed_tipo').value = btn.dataset.tipo;
                    document.getElementById('ed_data_vencimento').value = btn.dataset.data;
                    document.getElementById('ed_periodicidade').value = btn.dataset.periodicidade || '';
                    document.getElementById('ed_recorrente').checked = btn.dataset.recorrente === '1';
                    document.getElementById('modalEditDespesa').classList.add('open');
                });
            });

            movimentacoesContainer.querySelectorAll('form[data-ajax-pagar]').forEach((form) => {
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();

                    if (!confirm('Confirmar que esta despesa foi paga?')) {
                        return;
                    }

                    try {
                        await enviarFormularioAjax(form);

                        await carregarMovimentacoes(mesCampo.value, anoCampo.value);
                    } catch (error) {
                        form.submit();
                    }
                });
            });

            movimentacoesContainer.querySelectorAll('form[data-ajax-delete]').forEach((form) => {
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();

                    if (!confirm('Excluir este registro?')) {
                        return;
                    }

                    try {
                        await enviarFormularioAjax(form);
                        await carregarMovimentacoes(mesCampo.value, anoCampo.value);
                    } catch (error) {
                        form.submit();
                    }
                });
            });
        }

        async function carregarMovimentacoes(mes, ano) {
            try {
                const query = new URLSearchParams({
                    mes: String(mes),
                    ano: String(ano),
                });

                const response = await fetch(`${movimentacoesUrl}?${query.toString()}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('Falha ao carregar movimentacoes');
                }

                const data = await response.json();

                movimentacoesContainer.innerHTML = data.html;
                document.getElementById('stat-receitas').textContent = formatCurrency(data.totalReceitas);
                document.getElementById('stat-despesas').textContent = formatCurrency(data.totalDespesas);
                document.getElementById('stat-saldo').textContent = formatCurrency(data.saldo);

                mesCampo.value = String(data.mes);
                anoCampo.value = String(data.ano);
                periodoInput.value = `${data.ano}-${String(data.mes).padStart(2, '0')}`;

                history.replaceState(null, '', `/?mes=${data.mes}&ano=${data.ano}`);

                bindMovimentacoesEvents();
                if (window.initFinanceMonthChart) {
                    window.initFinanceMonthChart(movimentacoesContainer);
                }
            } catch (error) {
                formFiltro.submit();
            }
        }

        function enviarComData(data) {
            mesCampo.value = String(data.getMonth() + 1);
            anoCampo.value = String(data.getFullYear());
            carregarMovimentacoes(mesCampo.value, anoCampo.value);
        }

        periodoInput.addEventListener('change', function() {
            if (!this.value) return;
            const [ano, mes] = this.value.split('-');
            anoCampo.value = String(Number(ano));
            mesCampo.value = String(Number(mes));
            carregarMovimentacoes(mesCampo.value, anoCampo.value);
        });

        document.getElementById('prevMonth').addEventListener('click', function() {
            const data = new Date(periodoInput.value + '-01T12:00:00');
            data.setMonth(data.getMonth() - 1);
            periodoInput.value = `${data.getFullYear()}-${String(data.getMonth() + 1).padStart(2, '0')}`;
            enviarComData(data);
        });

        document.getElementById('nextMonth').addEventListener('click', function() {
            const data = new Date(periodoInput.value + '-01T12:00:00');
            data.setMonth(data.getMonth() + 1);
            periodoInput.value = `${data.getFullYear()}-${String(data.getMonth() + 1).padStart(2, '0')}`;
            enviarComData(data);
        });

        document.querySelectorAll('[data-open-modal]').forEach((btn) => {
            btn.addEventListener('click', () => {
                document.getElementById(btn.dataset.openModal).classList.add('open');
            });
        });

        document.querySelectorAll('[data-close-modal]').forEach((btn) => {
            btn.addEventListener('click', () => {
                document.getElementById(btn.dataset.closeModal).classList.remove('open');
            });
        });

        document.querySelectorAll('.modal-backdrop').forEach((backdrop) => {
            backdrop.addEventListener('click', (event) => {
                if (event.target === backdrop) {
                    backdrop.classList.remove('open');
                }
            });
        });

        bindMovimentacoesEvents();

        formEditReceita.addEventListener('submit', async (event) => {
            event.preventDefault();

            try {
                await enviarFormularioAjax(formEditReceita);
                document.getElementById('modalEditReceita').classList.remove('open');
                await carregarMovimentacoes(mesCampo.value, anoCampo.value);
            } catch (error) {
                formEditReceita.submit();
            }
        });

        formEditDespesa.addEventListener('submit', async (event) => {
            event.preventDefault();

            try {
                await enviarFormularioAjax(formEditDespesa);
                document.getElementById('modalEditDespesa').classList.remove('open');
                await carregarMovimentacoes(mesCampo.value, anoCampo.value);
            } catch (error) {
                formEditDespesa.submit();
            }
        });

        const app = document.querySelector('.app');
        const shouldOpenReceitaModal = app?.dataset.openReceita === '1';
        const shouldOpenDespesaModal = app?.dataset.openDespesa === '1';

        if (shouldOpenReceitaModal) {
            document.getElementById('modalReceita').classList.add('open');
        }

        if (shouldOpenDespesaModal) {
            document.getElementById('modalDespesa').classList.add('open');
        }
    </script>
</body>

</html>