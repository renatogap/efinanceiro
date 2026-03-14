<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- SEO básico -->
    <title>PlanejaLar – Controle Financeiro Familiar</title>
    <meta name="description" content="Organize as finanças da sua família com facilidade. Cadastre receitas, despesas, acompanhe seu saldo e tenha o controle na palma da mão.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/') }}">

    <!-- Open Graph (WhatsApp, Facebook, LinkedIn…) -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:site_name" content="PlanejaLar">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:title" content="PlanejaLar – Controle Financeiro Familiar">
    <meta property="og:description" content="Organize as finanças da sua família com facilidade. Cadastre receitas, despesas, acompanhe seu saldo e tenha o controle na palma da mão.">
    <meta property="og:image" content="{{ url('/og-image.png') }}">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="PlanejaLar – controle financeiro familiar">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="PlanejaLar – Controle Financeiro Familiar">
    <meta name="twitter:description" content="Organize as finanças da sua família com facilidade. Cadastre receitas, despesas, acompanhe seu saldo e tenha o controle na palma da mão.">
    <meta name="twitter:image" content="{{ url('/og-image.png') }}">
    <meta name="twitter:image:alt" content="PlanejaLar – controle financeiro familiar">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/favicon-192x192.png">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/js/app.js'])
    @endif
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body>
    @php
    $openReceitaModal = $errors->any() && old('_origin') === 'create_receita';
    $openDespesaModal = $errors->any() && old('_origin') === 'create_despesa';
    $filtroMesReferencia = mb_strtoupper(\Carbon\Carbon::parse($fimSelecionado)->locale('pt_BR')->translatedFormat('F/Y'), 'UTF-8');
    @endphp

    <div
        class="app"
        data-open-receita="{{ $openReceitaModal ? '1' : '0' }}"
        data-open-despesa="{{ $openDespesaModal ? '1' : '0' }}"
        data-movimentacoes-url="{{ route('financeiro.movimentacoes') }}"
        data-categoria-delete-url="{{ url('/categorias-despesa') }}"
        data-categorias-despesa='@json($categoriasDespesaPayload)'
    >
        <section class="header">
            <div class="header-top">
                <div class="header-brand">
                    <img src="/icone-app.png" alt="Logo PlanejaLar" class="app-logo">
                    <div>
                        <h1>PlanejaLar</h1>
                        <p>Controle suas finanças na palma da mão.</p>
                    </div>
                </div>
                <button class="header-visibility-btn" id="toggleValoresGlobais" type="button" aria-label="Ocultar valores">
                    <span class="material-icons-round">visibility</span>
                </button>
            </div>
            <div class="stats">
                <div class="stat">
                    <div class="label">Receitas</div>
                    <div class="value" id="stat-receitas" data-visible-value="R$ {{ number_format($totalReceitas, 2, ',', '.') }}">R$ {{ number_format($totalReceitas, 2, ',', '.') }}</div>
                </div>
                <div class="stat">
                    <div class="label">Despesas</div>
                    <div class="value" id="stat-despesas" data-visible-value="R$ {{ number_format($totalDespesas, 2, ',', '.') }}">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</div>
                </div>
                <div class="stat">
                    <div class="label">Saldo</div>
                    <div class="value" id="stat-saldo" data-visible-value="R$ {{ number_format($saldo, 2, ',', '.') }}">R$ {{ number_format($saldo, 2, ',', '.') }}</div>
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
                <h2 class="card-title filter-title-row">
                    <span class="card-title-main">
                        <span class="material-icons-round">tune</span>
                        Filtro por periodo
                    </span>
                    <span id="filtroMesReferencia" class="badge-warning">{{ $filtroMesReferencia }}</span>
                </h2>
                <form id="formFiltro" method="GET" action="{{ route('financeiro.index') }}" style="display:block;">
                    <input type="hidden" name="inicio" id="inicioCampo" value="{{ $inicioSelecionado }}">
                    <input type="hidden" name="fim" id="fimCampo" value="{{ $fimSelecionado }}">
                    <div class="filter-row">
                        <button class="arrow-btn" type="button" id="prevMonth" aria-label="Mes anterior">
                            <span class="material-icons-round">chevron_left</span>
                        </button>
                        <input class="month-input" type="text" id="periodoInput" value="{{ $periodoLabel }}" readonly aria-label="Selecionar periodo">
                        <button class="arrow-btn" type="button" id="nextMonth" aria-label="Proximo mes">
                            <span class="material-icons-round">chevron_right</span>
                        </button>
                    </div>
                </form>
            </article>

            <div id="movimentacoesContainer" class="card-wide">
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
                        <input id="r_valor" name="valor" type="text" inputmode="decimal" placeholder="R$ 0,00" data-money value="{{ old('valor') }}" required>
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
                <div class="field">
                    <label for="d_categoria_despesa_id">Categoria</label>
                    <div class="category-input-row">
                        <select id="d_categoria_despesa_id" name="categoria_despesa_id" data-categoria-select required>
                            <option value="">Selecione uma categoria</option>
                            @foreach ($categoriasDespesa as $categoria)
                            <option value="{{ $categoria->id }}" @selected(old('categoria_despesa_id') == $categoria->id)>{{ $categoria->nome }}</option>
                            @endforeach
                        </select>
                        <button class="icon-btn inline-action" type="button" data-open-modal="modalCategoriaDespesa" aria-label="Cadastrar nova categoria de despesa">
                            <span class="material-icons-round">add</span>
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="field">
                        <label for="d_valor">Valor</label>
                        <input id="d_valor" name="valor" type="text" inputmode="decimal" placeholder="R$ 0,00" data-money required>
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
                        <label for="d_data_vencimento">Data do vencimento</label>
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
                <label class="toggle" for="d_eh_cartao_credito">
                    <input id="d_eh_cartao_credito" name="eh_cartao_credito" type="checkbox" value="1" @checked(old('eh_cartao_credito'))>
                    Vincular a um Cartao de Credito
                </label>
                <div class="field" id="d_cartao_credito_wrap" style="display: none;">
                    <label for="d_cartao_credito_nome">Qual cartao?</label>
                    <select id="d_cartao_credito_nome" name="cartao_credito_nome">
                        <option value="">Selecione</option>
                        <option value="inter" @selected(old('cartao_credito_nome') === 'inter')>Inter</option>
                        <option value="santander" @selected(old('cartao_credito_nome') === 'santander')>Santander</option>
                    </select>
                </div>
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
                        <input id="er_valor" name="valor" type="text" inputmode="decimal" placeholder="R$ 0,00" data-money required>
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
            <form id="formEditDespesa" method="POST">
                @csrf
                @method('PUT')
                <div class="field">
                    <label for="ed_descricao">Descricao</label>
                    <input id="ed_descricao" name="descricao" type="text" required>
                </div>
                <div class="field">
                    <label for="ed_categoria_despesa_id">Categoria</label>
                    <div class="category-input-row">
                        <select id="ed_categoria_despesa_id" name="categoria_despesa_id" data-categoria-select required>
                            <option value="">Selecione uma categoria</option>
                            @foreach ($categoriasDespesa as $categoria)
                            <option value="{{ $categoria->id }}">{{ $categoria->nome }}</option>
                            @endforeach
                        </select>
                        <button class="icon-btn inline-action" type="button" data-open-modal="modalCategoriaDespesa" aria-label="Cadastrar nova categoria de despesa">
                            <span class="material-icons-round">add</span>
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="field">
                        <label for="ed_valor">Valor</label>
                        <input id="ed_valor" name="valor" type="text" inputmode="decimal" placeholder="R$ 0,00" data-money required>
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
                        <label for="ed_data_vencimento">Data do vencimento</label>
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
                <label class="toggle" for="ed_eh_cartao_credito">
                    <input id="ed_eh_cartao_credito" name="eh_cartao_credito" type="checkbox" value="1">
                    Vincular a um Cartao de Credito
                </label>
                <div class="field" id="ed_cartao_credito_wrap" style="display: none;">
                    <label for="ed_cartao_credito_nome">Qual cartao?</label>
                    <select id="ed_cartao_credito_nome" name="cartao_credito_nome">
                        <option value="">Selecione</option>
                        <option value="inter">Inter</option>
                        <option value="santander">Santander</option>
                    </select>
                </div>
                <input type="hidden" id="ed_alterar_futuras" name="_alterar_futuras" value="0">
                <button class="primary-btn" type="submit">
                    <span class="material-icons-round">save</span>
                    Atualizar
                </button>
            </form>
        </div>
    </div>

    <div class="modal-backdrop" id="modalPagamentoDespesa">
        <div class="modal" style="max-width: 380px;">
            <div class="modal-head">
                <h3>Registrar pagamento</h3>
                <div style="display:flex; gap:8px;">
                    <button class="icon-btn danger" id="btnRemoverPagamento" type="button" aria-label="Excluir pagamento" style="display:none;">
                        <span class="material-icons-round">delete</span>
                    </button>
                    <button class="icon-btn" type="button" data-close-modal="modalPagamentoDespesa" aria-label="Fechar">
                        <span class="material-icons-round">close</span>
                    </button>
                </div>
            </div>
            <form id="formPagamentoDespesa" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="_remover_pagamento" id="pd_remover_pagamento" value="0">
                <div class="field">
                    <label for="pd_forma_pagamento">Forma de pagamento</label>
                    <select id="pd_forma_pagamento" name="forma_pagamento" required>
                        <option value="">Selecione</option>
                        <option value="cartao_credito">Cartao de Credito</option>
                        <option value="cartao_debito">Cartao de Debito</option>
                        <option value="pix">Pix</option>
                        <option value="dinheiro">Dinheiro</option>
                        <option value="boleto">Boleto</option>
                        <option value="transferencia">Transferencia</option>
                    </select>
                </div>
                <button class="primary-btn" type="submit">
                    <span class="material-icons-round">task_alt</span>
                    Confirmar pagamento
                </button>
            </form>
        </div>
    </div>

    <div class="modal-backdrop" id="modalConfirmarAlteracaoValor" style="z-index: 50; background: rgba(2, 6, 23, 0.35);">
        <div class="modal" style="max-width: 380px;">
            <div class="modal-head">
                <h3>Aplicar alteracoes</h3>
                <button class="icon-btn" type="button" id="btnFecharConfirmacaoValor" aria-label="Cancelar">
                    <span class="material-icons-round">close</span>
                </button>
            </div>
            <p style="margin: 0 0 16px; font-size: 0.88rem; color: #475569; line-height: 1.5;">
                Esta despesa e recorrente. Deseja aplicar as alteracoes somente neste lancamento ou tambem para todos os proximos meses?
            </p>
            <div style="display: grid; gap: 8px;">
                <button class="primary-btn" id="btnAlterarSomenteEsta" type="button">
                    <span class="material-icons-round">event</span>
                    Somente este lancamento
                </button>
                <button class="secondary-btn" id="btnAlterarTodasFuturas" type="button">
                    <span class="material-icons-round">repeat</span>
                    Este e os proximos lancamentos
                </button>
            </div>
        </div>
    </div>

    <div class="modal-backdrop" id="modalConfirmarExclusaoRecorrencia" style="z-index: 50; background: rgba(2, 6, 23, 0.35);">
        <div class="modal" style="max-width: 380px;">
            <div class="modal-head">
                <h3>Excluir despesa recorrente</h3>
                <button class="icon-btn" type="button" id="btnFecharConfirmacaoExclusao" aria-label="Cancelar">
                    <span class="material-icons-round">close</span>
                </button>
            </div>
            <p id="textoConfirmacaoExclusaoRecorrencia" style="margin: 0 0 16px; font-size: 0.88rem; color: #475569; line-height: 1.5;">
                Existem despesas recorrentes nos meses posteriores. Deseja excluir somente esta despesa ou tambem esta e as posteriores?
            </p>
            <div style="display: grid; gap: 8px;">
                <button class="primary-btn" id="btnExcluirSomenteEstaDespesa" type="button">
                    <span class="material-icons-round">event</span>
                    Somente esta despesa
                </button>
                <button class="secondary-btn" id="btnExcluirEstaEDemais" type="button">
                    <span class="material-icons-round">repeat</span>
                    Esta e as posteriores
                </button>
            </div>
        </div>
    </div>

    <div class="modal-backdrop" id="modalCategoriaDespesa">
        <div class="modal" style="max-width: 380px;">
            <div class="modal-head">
                <h3>Nova Categoria</h3>
                <button class="icon-btn" type="button" data-close-modal="modalCategoriaDespesa" aria-label="Fechar">
                    <span class="material-icons-round">close</span>
                </button>
            </div>
            <form id="formCategoriaDespesa" action="{{ route('categorias-despesa.store') }}" method="POST">
                @csrf
                <div class="field">
                    <label for="cd_nome">Nome da categoria</label>
                    <input id="cd_nome" name="nome" type="text" maxlength="60" placeholder="Ex.: Pet, Viagem, Impostos" required>
                </div>
                <button class="primary-btn" type="submit">
                    <span class="material-icons-round">save</span>
                    Salvar categoria
                </button>
            </form>
            <div class="category-manager">
                <div class="category-manager-head">
                    <strong>Categorias cadastradas</strong>
                    <span class="badge-warning" id="categoriaCountBadge">{{ count($categoriasDespesaPayload) }}</span>
                </div>
                <p class="category-help">Para excluir uma categoria em uso, escolha antes para qual categoria as despesas serao movidas.</p>
                <div class="category-transfer" id="categoryTransferBox">
                    <p id="categoryTransferText"></p>
                    <select id="categoriaDestinoSelect">
                        <option value="">Selecione a categoria de destino</option>
                    </select>
                    <div class="category-transfer-actions">
                        <button class="secondary-btn" id="btnConfirmarExclusaoCategoria" type="button">Mover e excluir</button>
                        <button class="icon-btn" id="btnCancelarExclusaoCategoria" type="button">Cancelar</button>
                    </div>
                </div>
                <div class="category-list" id="categoryList"></div>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/script.js') }}?v={{ filemtime(public_path('js/script.js')) }}" defer></script>
</body>

</html>