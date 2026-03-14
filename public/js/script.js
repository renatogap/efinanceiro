        document.addEventListener('DOMContentLoaded', () => {
        const flatpickrLib = window.flatpickr;
        if (!flatpickrLib) {
            console.error('flatpickr nao foi carregado pelo bundle local.');
            return;
        }

        const app = document.querySelector('.app');
        if (!app) {
            return;
        }

        const formFiltro = document.getElementById('formFiltro');
        const periodoInput = document.getElementById('periodoInput');
        const inicioCampo = document.getElementById('inicioCampo');
        const fimCampo = document.getElementById('fimCampo');
        const movimentacoesContainer = document.getElementById('movimentacoesContainer');
        const movimentacoesUrl = app.dataset.movimentacoesUrl || '/movimentacoes';
        const formEditReceita = document.getElementById('formEditReceita');
        const formEditDespesa = document.getElementById('formEditDespesa');
        const formCreateDespesa = document.querySelector('#modalDespesa form');
        const formCategoriaDespesa = document.getElementById('formCategoriaDespesa');
        const formPagamentoDespesa = document.getElementById('formPagamentoDespesa');
        const btnRemoverPagamento = document.getElementById('btnRemoverPagamento');
        const pdFormaPagamento = document.getElementById('pd_forma_pagamento');
        const pdRemoverPagamento = document.getElementById('pd_remover_pagamento');
        const filtroMesReferencia = document.getElementById('filtroMesReferencia');
        const statReceitasEl = document.getElementById('stat-receitas');
        const statDespesasEl = document.getElementById('stat-despesas');
        const statSaldoEl = document.getElementById('stat-saldo');
        const toggleValoresGlobaisBtn = document.getElementById('toggleValoresGlobais');
        const categoryList = document.getElementById('categoryList');
        const categoryTransferBox = document.getElementById('categoryTransferBox');
        const categoryTransferText = document.getElementById('categoryTransferText');
        const categoriaDestinoSelect = document.getElementById('categoriaDestinoSelect');
        const categoriaCountBadge = document.getElementById('categoriaCountBadge');
        const categoriaDeleteUrlBase = app.dataset.categoriaDeleteUrl || '/categorias-despesa';
        const categoriasDespesaStateInicial = JSON.parse(app.dataset.categoriasDespesa || '[]');
        const modalConfirmarExclusaoRecorrencia = document.getElementById('modalConfirmarExclusaoRecorrencia');
        const textoConfirmacaoExclusaoRecorrencia = document.getElementById('textoConfirmacaoExclusaoRecorrencia');
        const btnExcluirSomenteEstaDespesa = document.getElementById('btnExcluirSomenteEstaDespesa');
        const btnExcluirEstaEDemais = document.getElementById('btnExcluirEstaEDemais');
        const dEhCartaoCredito = document.getElementById('d_eh_cartao_credito');
        const dCartaoCreditoWrap = document.getElementById('d_cartao_credito_wrap');
        const dCartaoCreditoNome = document.getElementById('d_cartao_credito_nome');
        const edEhCartaoCredito = document.getElementById('ed_eh_cartao_credito');
        const edCartaoCreditoWrap = document.getElementById('ed_cartao_credito_wrap');
        const edCartaoCreditoNome = document.getElementById('ed_cartao_credito_nome');
        let categoriasDespesaState = Array.isArray(categoriasDespesaStateInicial) ? categoriasDespesaStateInicial : [];
        let categoriaPendenteExclusao = null;
        let formPendenteExclusaoRecorrencia = null;
        let valoresVisiveis = true;
        const valoresVisiveisStorageKey = 'planejalar.valoresVisiveis';
        let periodPicker;

        function carregarPreferenciaVisibilidade() {
            try {
                const savedValue = window.localStorage.getItem(valoresVisiveisStorageKey);
                if (savedValue === '0') {
                    valoresVisiveis = false;
                } else if (savedValue === '1') {
                    valoresVisiveis = true;
                }
            } catch (error) {
                // Ignora falhas de acesso ao localStorage.
            }
        }

        function salvarPreferenciaVisibilidade() {
            try {
                window.localStorage.setItem(valoresVisiveisStorageKey, valoresVisiveis ? '1' : '0');
            } catch (error) {
                // Ignora falhas de acesso ao localStorage.
            }
        }

        function formatCurrency(valor) {
            return Number(valor || 0).toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL',
            });
        }

        function rebuildCategoriaOptions(categorias, selectedValue = '') {
            document.querySelectorAll('[data-categoria-select]').forEach((select) => {
                select.innerHTML = '<option value="">Selecione uma categoria</option>';

                categorias.forEach((categoria) => {
                    const option = document.createElement('option');
                    option.value = String(categoria.id);
                    option.textContent = categoria.nome;
                    option.selected = String(categoria.id) === String(selectedValue);
                    select.appendChild(option);
                });
            });
        }

        function fecharTransferenciaCategoria() {
            categoriaPendenteExclusao = null;
            categoryTransferBox.classList.remove('open');
            categoryTransferText.textContent = '';
            categoriaDestinoSelect.innerHTML = '<option value="">Selecione a categoria de destino</option>';
        }

        function abrirTransferenciaCategoria(categoria) {
            categoriaPendenteExclusao = categoria;
            categoryTransferText.textContent = `A categoria ${categoria.nome} possui ${categoria.despesas_count} despesa(s). Escolha a categoria de destino antes de excluir.`;
            categoriaDestinoSelect.innerHTML = '<option value="">Selecione a categoria de destino</option>';

            categoriasDespesaState
                .filter((item) => String(item.id) !== String(categoria.id))
                .forEach((item) => {
                    const option = document.createElement('option');
                    option.value = String(item.id);
                    option.textContent = item.nome;
                    categoriaDestinoSelect.appendChild(option);
                });

            categoryTransferBox.classList.add('open');
        }

        function renderCategoryList() {
            categoryList.innerHTML = '';
            categoriaCountBadge.textContent = String(categoriasDespesaState.length);

            categoriasDespesaState.forEach((categoria) => {
                const row = document.createElement('div');
                row.className = 'category-row';

                const main = document.createElement('div');
                main.className = 'category-row-main';

                const title = document.createElement('div');
                title.className = 'category-row-title';
                title.textContent = categoria.nome;

                const meta = document.createElement('div');
                meta.className = 'category-row-meta';
                meta.textContent = categoria.is_protected
                    ? 'Categoria padrao protegida'
                    : `${categoria.despesas_count} despesa(s) vinculada(s)`;

                const action = document.createElement('button');
                action.type = 'button';
                action.className = 'icon-btn danger';
                action.setAttribute('aria-label', `Excluir categoria ${categoria.nome}`);
                action.innerHTML = '<span class="material-icons-round">delete</span>';
                action.disabled = !!categoria.is_protected;

                if (categoria.is_protected) {
                    action.style.opacity = '0.45';
                    action.style.cursor = 'not-allowed';
                } else {
                    action.addEventListener('click', async () => {
                        if (Number(categoria.despesas_count) > 0) {
                            abrirTransferenciaCategoria(categoria);
                            return;
                        }

                        if (!confirm(`Excluir a categoria ${categoria.nome}?`)) {
                            return;
                        }

                        await excluirCategoria(categoria.id);
                    });
                }

                main.appendChild(title);
                main.appendChild(meta);
                row.appendChild(main);
                row.appendChild(action);
                categoryList.appendChild(row);
            });
        }

        async function excluirCategoria(categoriaId, categoriaDestinoId = '') {
            const formData = new FormData();
            formData.append('_method', 'DELETE');
            formData.append('_token', formCategoriaDespesa.querySelector('input[name="_token"]').value);

            if (categoriaDestinoId) {
                formData.append('categoria_destino_id', String(categoriaDestinoId));
            }

            const response = await fetch(`${categoriaDeleteUrlBase}/${categoriaId}`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Falha ao excluir categoria');
            }

            categoriasDespesaState = data.categorias;
            rebuildCategoriaOptions(categoriasDespesaState);
            renderCategoryList();
            fecharTransferenciaCategoria();
        }

        function formatMoneyInput(input, value) {
            const num = parseFloat(String(value || '').replace(',', '.'));
            input.value = isNaN(num) ? '' : num.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function setupMoneyMask(input) {
            if (input.value) {
                formatMoneyInput(input, input.value);
            }
            input.addEventListener('input', function() {
                const digits = this.value.replace(/\D/g, '');
                this.value = digits ? (parseInt(digits, 10) / 100).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '';
            });
        }

        function atualizarCamposCartaoCredito(prefix) {
            const isCreate = prefix === 'd';
            const checkbox = isCreate ? dEhCartaoCredito : edEhCartaoCredito;
            const wrap = isCreate ? dCartaoCreditoWrap : edCartaoCreditoWrap;
            const select = isCreate ? dCartaoCreditoNome : edCartaoCreditoNome;

            if (!checkbox || !wrap || !select) {
                return;
            }

            const ativo = checkbox.checked;
            wrap.style.display = ativo ? 'block' : 'none';
            select.required = ativo;

            if (!ativo) {
                select.value = '';
            }
        }

        function aplicarVisibilidadeReceitas() {
            if (statReceitasEl) {
                if (!statReceitasEl.dataset.visibleValue) {
                    statReceitasEl.dataset.visibleValue = statReceitasEl.textContent.trim();
                }

                statReceitasEl.textContent = valoresVisiveis
                    ? statReceitasEl.dataset.visibleValue
                    : 'R$ •••••';
            }

            if (statSaldoEl) {
                if (!statSaldoEl.dataset.visibleValue) {
                    statSaldoEl.dataset.visibleValue = statSaldoEl.textContent.trim();
                }

                statSaldoEl.textContent = valoresVisiveis
                    ? statSaldoEl.dataset.visibleValue
                    : 'R$ •••••';
            }

            movimentacoesContainer.querySelectorAll('.receita-amount').forEach((el) => {
                if (!el.dataset.visibleValue) {
                    el.dataset.visibleValue = el.textContent.trim();
                }

                el.textContent = valoresVisiveis
                    ? el.dataset.visibleValue
                    : '+ R$ •••••';
            });
        }

        function aplicarVisibilidadeDespesas() {
            if (statDespesasEl) {
                if (!statDespesasEl.dataset.visibleValue) {
                    statDespesasEl.dataset.visibleValue = statDespesasEl.textContent.trim();
                }

                statDespesasEl.textContent = valoresVisiveis
                    ? statDespesasEl.dataset.visibleValue
                    : 'R$ •••••';
            }

            movimentacoesContainer.querySelectorAll('.despesa-amount').forEach((el) => {
                if (!el.dataset.visibleValue) {
                    el.dataset.visibleValue = el.textContent.trim();
                }

                el.textContent = valoresVisiveis
                    ? el.dataset.visibleValue
                    : '- R$ •••••';
            });
        }

        function aplicarVisibilidadeResumoGrafico() {
            movimentacoesContainer.querySelectorAll('.chart-pill').forEach((pill) => {
                if (!pill.dataset.visibleValue) {
                    pill.dataset.visibleValue = pill.textContent.trim();
                }

                if (!pill.dataset.maskLabel) {
                    const texto = pill.dataset.visibleValue;
                    const indiceSeparador = texto.indexOf(':');
                    pill.dataset.maskLabel = indiceSeparador >= 0
                        ? texto.slice(0, indiceSeparador + 1)
                        : 'Valor:';
                }

                pill.textContent = valoresVisiveis
                    ? pill.dataset.visibleValue
                    : `${pill.dataset.maskLabel} R$ •••••`;
            });

            if (window.setFinanceChartValuesVisibility) {
                window.setFinanceChartValuesVisibility(valoresVisiveis);
            }
        }

        function aplicarVisibilidadeGlobal() {
            aplicarVisibilidadeReceitas();
            aplicarVisibilidadeDespesas();
            aplicarVisibilidadeResumoGrafico();

            if (toggleValoresGlobaisBtn) {
                const icon = toggleValoresGlobaisBtn.querySelector('.material-icons-round');
                if (icon) {
                    icon.textContent = valoresVisiveis ? 'visibility' : 'visibility_off';
                }
                toggleValoresGlobaisBtn.setAttribute(
                    'aria-label',
                    valoresVisiveis ? 'Ocultar valores' : 'Mostrar valores'
                );
            }
        }

        function parseMoneyFieldValue(value) {
            const parsed = parseFloat(String(value || '').replace(/\./g, '').replace(',', '.'));
            return Number.isFinite(parsed) ? parsed : 0;
        }

        function parseIsoDate(value) {
            const [year, month, day] = String(value || '').split('-').map(Number);
            if (!year || !month || !day) return null;
            return new Date(year, month - 1, day, 12, 0, 0);
        }

        function toIsoDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function formatDateBr(date) {
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }

        function formatPeriodoLabel(inicio, fim) {
            return `${formatDateBr(inicio)} - ${formatDateBr(fim)}`;
        }

        function formatMonthYear(date) {
            const month = date.toLocaleString('pt-BR', { month: 'long' }).toLocaleUpperCase('pt-BR');
            return `${month}/${date.getFullYear()}`;
        }

        function addMonthsNoOverflow(date, delta) {
            const targetMonth = date.getMonth() + delta;
            const base = new Date(date.getFullYear(), targetMonth, 1, 12, 0, 0);
            const lastDay = new Date(base.getFullYear(), base.getMonth() + 1, 0).getDate();
            return new Date(base.getFullYear(), base.getMonth(), Math.min(date.getDate(), lastDay), 12, 0, 0);
        }

        function obterPeriodoAtual() {
            const inicio = parseIsoDate(inicioCampo.value);
            const fim = parseIsoDate(fimCampo.value);
            return {
                inicio: inicio || new Date(),
                fim: fim || new Date(),
            };
        }

        function atualizarPeriodoCampos(inicio, fim, syncPicker = true) {
            inicioCampo.value = toIsoDate(inicio);
            fimCampo.value = toIsoDate(fim);
            periodoInput.value = formatPeriodoLabel(inicio, fim);
            if (filtroMesReferencia) {
                filtroMesReferencia.textContent = formatMonthYear(fim);
            }

            if (syncPicker && periodPicker) {
                periodPicker.setDate([inicio, fim], false);
            }
        }

        async function enviarFormularioAjax(form) {
            const formData = new FormData(form);

            form.querySelectorAll('[data-money]').forEach(function(input) {
                if (input.name && input.value !== '') {
                    formData.set(input.name, input.value.replace(/\./g, '').replace(',', '.'));
                }
            });

            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });

            if (!response.ok) {
                throw new Error('Falha na requisicao');
            }
        }

        async function enviarExclusaoDespesa(form, excluirFuturas = false) {
            const inputExcluirFuturas = document.createElement('input');
            inputExcluirFuturas.type = 'hidden';
            inputExcluirFuturas.name = '_excluir_futuras';
            inputExcluirFuturas.value = excluirFuturas ? '1' : '0';
            form.appendChild(inputExcluirFuturas);

            try {
                await enviarFormularioAjax(form);
                await carregarMovimentacoes(inicioCampo.value, fimCampo.value);
            } catch (error) {
                form.submit();
            }
        }

        function fecharModalExclusaoRecorrencia() {
            formPendenteExclusaoRecorrencia = null;
            modalConfirmarExclusaoRecorrencia?.classList.remove('open');
        }

        function abrirModalExclusaoRecorrencia(form) {
            formPendenteExclusaoRecorrencia = form;
            const temFuturas = form.dataset.hasFuturas === '1';

            if (textoConfirmacaoExclusaoRecorrencia) {
                const descricao = form.dataset.descricao || 'esta despesa';
                textoConfirmacaoExclusaoRecorrencia.textContent = temFuturas
                    ? `A despesa ${descricao} possui lancamentos recorrentes nos meses posteriores. Deseja excluir somente esta despesa ou tambem esta e as posteriores?`
                    : `Deseja excluir a despesa ${descricao}?`;
            }

            if (btnExcluirSomenteEstaDespesa) {
                btnExcluirSomenteEstaDespesa.innerHTML = temFuturas
                    ? '<span class="material-icons-round">event</span>Somente esta despesa'
                    : '<span class="material-icons-round">delete</span>Excluir despesa';
            }

            if (btnExcluirEstaEDemais) {
                btnExcluirEstaEDemais.style.display = temFuturas ? 'inline-flex' : 'none';
            }

            modalConfirmarExclusaoRecorrencia?.classList.add('open');
        }

        function bindMovimentacoesEvents() {
            function abrirModalPagamento(action, formaPagamento = '', permiteRemover = false) {
                formPagamentoDespesa.action = action;
                pdFormaPagamento.value = formaPagamento;
                pdFormaPagamento.required = true;
                pdRemoverPagamento.value = '0';
                btnRemoverPagamento.style.display = permiteRemover ? 'inline-flex' : 'none';
                document.getElementById('modalPagamentoDespesa').classList.add('open');
            }

            movimentacoesContainer.querySelectorAll('[data-open-inline-modal]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const modalId = btn.dataset.openInlineModal;
                    if (modalId) {
                        document.getElementById(modalId)?.classList.add('open');
                    }
                });
            });

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
                    const deveAbrir = !item.classList.contains('open');

                    movimentacoesContainer.querySelectorAll('[data-item].open').forEach((aberto) => {
                        if (aberto !== item) {
                            aberto.classList.remove('open');
                        }
                    });

                    item.classList.toggle('open', deveAbrir);
                });
            });

            movimentacoesContainer.querySelectorAll('[data-edit-receita]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    formEditReceita.action = `/receitas/${btn.dataset.id}`;
                    document.getElementById('er_descricao').value = btn.dataset.descricao;
                    formatMoneyInput(document.getElementById('er_valor'), btn.dataset.valor);
                    document.getElementById('er_data_credito').value = btn.dataset.data;
                    document.getElementById('er_fonte').value = btn.dataset.fonte || '';
                    document.getElementById('modalEditReceita').classList.add('open');
                });
            });

            movimentacoesContainer.querySelectorAll('[data-edit-despesa]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    formEditDespesa.action = `/despesas/${btn.dataset.id}`;
                    formEditDespesa.dataset.originalState = JSON.stringify({
                        descricao: btn.dataset.descricao || '',
                        categoria_despesa_id: btn.dataset.categoria || '',
                        valor: String(btn.dataset.valor || '0'),
                        tipo: btn.dataset.tipo || '',
                        eh_cartao_credito: btn.dataset.ehCartaoCredito === '1',
                        cartao_credito_nome: btn.dataset.cartaoCreditoNome || '',
                        data_vencimento: btn.dataset.data || '',
                        periodicidade: btn.dataset.periodicidade || '',
                        recorrente: btn.dataset.recorrente === '1',
                    });
                    document.getElementById('ed_descricao').value = btn.dataset.descricao;
                    document.getElementById('ed_categoria_despesa_id').value = btn.dataset.categoria || '';
                    formatMoneyInput(document.getElementById('ed_valor'), btn.dataset.valor);
                    document.getElementById('ed_tipo').value = btn.dataset.tipo;
                    edEhCartaoCredito.checked = btn.dataset.ehCartaoCredito === '1';
                    edCartaoCreditoNome.value = btn.dataset.cartaoCreditoNome || '';
                    document.getElementById('ed_data_vencimento').value = btn.dataset.data;
                    document.getElementById('ed_periodicidade').value = btn.dataset.periodicidade || '';
                    document.getElementById('ed_recorrente').checked = btn.dataset.recorrente === '1';
                    atualizarCamposCartaoCredito('ed');
                    document.getElementById('modalEditDespesa').classList.add('open');
                });
            });

            movimentacoesContainer.querySelectorAll('form[data-ajax-pagar]').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    event.preventDefault();

                    abrirModalPagamento(form.action, '', false);
                });
            });

            movimentacoesContainer.querySelectorAll('[data-edit-pagamento]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    abrirModalPagamento(`/despesas/${btn.dataset.id}/pagar`, btn.dataset.formaPagamento || '', true);
                });
            });

            movimentacoesContainer.querySelectorAll('form[data-ajax-delete-receita]').forEach((form) => {
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();

                    if (!confirm('Excluir este registro?')) {
                        return;
                    }

                    await enviarExclusaoDespesa(form, false);
                });
            });

            movimentacoesContainer.querySelectorAll('form[data-ajax-delete-despesa]').forEach((form) => {
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    abrirModalExclusaoRecorrencia(form);
                });
            });

            aplicarVisibilidadeGlobal();
        }

        async function carregarMovimentacoes(inicio, fim) {
            try {
                const query = new URLSearchParams({
                    inicio: String(inicio),
                    fim: String(fim),
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
                statReceitasEl.dataset.visibleValue = formatCurrency(data.totalReceitas);
                statReceitasEl.textContent = statReceitasEl.dataset.visibleValue;
                statDespesasEl.dataset.visibleValue = formatCurrency(data.totalDespesas);
                statDespesasEl.textContent = statDespesasEl.dataset.visibleValue;
                statSaldoEl.dataset.visibleValue = formatCurrency(data.saldo);
                statSaldoEl.textContent = statSaldoEl.dataset.visibleValue;

                const inicioData = parseIsoDate(data.inicio);
                const fimData = parseIsoDate(data.fim);

                if (inicioData && fimData) {
                    atualizarPeriodoCampos(inicioData, fimData);
                }

                history.replaceState(null, '', `/?inicio=${data.inicio}&fim=${data.fim}`);

                bindMovimentacoesEvents();
                if (window.initFinanceMonthChart) {
                    window.initFinanceMonthChart(movimentacoesContainer);
                }
            } catch (error) {
                formFiltro.submit();
            }
        }

        periodPicker = flatpickrLib(periodoInput, {
            mode: 'range',
            dateFormat: 'd/m/Y',
            locale: 'pt',
            clickOpens: true,
            defaultDate: [inicioCampo.value, fimCampo.value],
            onClose: function(selectedDates) {
                if (selectedDates.length !== 2) {
                    return;
                }

                const [inicio, fim] = selectedDates[0] <= selectedDates[1]
                    ? [selectedDates[0], selectedDates[1]]
                    : [selectedDates[1], selectedDates[0]];

                atualizarPeriodoCampos(inicio, fim, false);
                carregarMovimentacoes(inicioCampo.value, fimCampo.value);
            },
        });

        const periodoInicial = obterPeriodoAtual();
        atualizarPeriodoCampos(periodoInicial.inicio, periodoInicial.fim);

        document.getElementById('prevMonth').addEventListener('click', function() {
            const { inicio, fim } = obterPeriodoAtual();
            const novoInicio = addMonthsNoOverflow(inicio, -1);
            const novoFim = addMonthsNoOverflow(fim, -1);
            atualizarPeriodoCampos(novoInicio, novoFim);
            carregarMovimentacoes(inicioCampo.value, fimCampo.value);
        });

        document.getElementById('nextMonth').addEventListener('click', function() {
            const { inicio, fim } = obterPeriodoAtual();
            const novoInicio = addMonthsNoOverflow(inicio, 1);
            const novoFim = addMonthsNoOverflow(fim, 1);
            atualizarPeriodoCampos(novoInicio, novoFim);
            carregarMovimentacoes(inicioCampo.value, fimCampo.value);
        });

        if (toggleValoresGlobaisBtn) {
            toggleValoresGlobaisBtn.addEventListener('click', () => {
                valoresVisiveis = !valoresVisiveis;
                salvarPreferenciaVisibilidade();
                aplicarVisibilidadeGlobal();
            });
        }

        carregarPreferenciaVisibilidade();

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

        formCategoriaDespesa.addEventListener('submit', async (event) => {
            event.preventDefault();

            try {
                const response = await fetch(formCategoriaDespesa.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(formCategoriaDespesa),
                });

                if (!response.ok) {
                    throw new Error('Falha ao cadastrar categoria');
                }

                const data = await response.json();
                categoriasDespesaState = data.categorias;
                rebuildCategoriaOptions(categoriasDespesaState, data.categoria.id);
                renderCategoryList();
                document.getElementById('d_categoria_despesa_id').value = String(data.categoria.id);
                document.getElementById('ed_categoria_despesa_id').value = String(data.categoria.id);
                document.getElementById('modalCategoriaDespesa').classList.remove('open');
                fecharTransferenciaCategoria();
                formCategoriaDespesa.reset();
            } catch (error) {
                formCategoriaDespesa.submit();
            }
        });

        formPagamentoDespesa.addEventListener('submit', async (event) => {
            event.preventDefault();

            try {
                await enviarFormularioAjax(formPagamentoDespesa);
                document.getElementById('modalPagamentoDespesa').classList.remove('open');
                await carregarMovimentacoes(inicioCampo.value, fimCampo.value);
            } catch (error) {
                formPagamentoDespesa.submit();
            }
        });

        btnRemoverPagamento.addEventListener('click', async () => {
            if (!confirm('Deseja excluir o pagamento desta despesa?')) {
                return;
            }

            try {
                pdRemoverPagamento.value = '1';
                pdFormaPagamento.required = false;
                await enviarFormularioAjax(formPagamentoDespesa);
                document.getElementById('modalPagamentoDespesa').classList.remove('open');
                await carregarMovimentacoes(inicioCampo.value, fimCampo.value);
            } catch (error) {
                formPagamentoDespesa.submit();
            } finally {
                pdRemoverPagamento.value = '0';
                pdFormaPagamento.required = true;
            }
        });

        document.getElementById('btnCancelarExclusaoCategoria').addEventListener('click', () => {
            fecharTransferenciaCategoria();
        });

        document.getElementById('btnConfirmarExclusaoCategoria').addEventListener('click', async () => {
            if (!categoriaPendenteExclusao || !categoriaDestinoSelect.value) {
                return;
            }

            try {
                await excluirCategoria(categoriaPendenteExclusao.id, categoriaDestinoSelect.value);
            } catch (error) {
                alert(error.message);
            }
        });

        renderCategoryList();

        bindMovimentacoesEvents();

        formEditReceita.addEventListener('submit', async (event) => {
            event.preventDefault();

            try {
                await enviarFormularioAjax(formEditReceita);
                document.getElementById('modalEditReceita').classList.remove('open');
                await carregarMovimentacoes(inicioCampo.value, fimCampo.value);
            } catch (error) {
                formEditReceita.submit();
            }
        });

        async function submitEditDespesa() {
            try {
                await enviarFormularioAjax(formEditDespesa);
                document.getElementById('modalEditDespesa').classList.remove('open');
                await carregarMovimentacoes(inicioCampo.value, fimCampo.value);
            } catch (error) {
                formEditDespesa.submit();
            }
        }

        formEditDespesa.addEventListener('submit', async (event) => {
            event.preventDefault();

            atualizarCamposCartaoCredito('ed');

            const originalState = JSON.parse(formEditDespesa.dataset.originalState || '{}');
            const estadoAtual = {
                descricao: document.getElementById('ed_descricao').value.trim(),
                categoria_despesa_id: document.getElementById('ed_categoria_despesa_id').value,
                valor: parseMoneyFieldValue(document.getElementById('ed_valor').value),
                tipo: document.getElementById('ed_tipo').value,
                eh_cartao_credito: edEhCartaoCredito.checked,
                cartao_credito_nome: edCartaoCreditoNome.value || '',
                data_vencimento: document.getElementById('ed_data_vencimento').value,
                periodicidade: document.getElementById('ed_periodicidade').value || '',
                recorrente: document.getElementById('ed_recorrente').checked,
            };

            const estadoOriginal = {
                descricao: String(originalState.descricao || '').trim(),
                categoria_despesa_id: String(originalState.categoria_despesa_id || ''),
                valor: parseFloat(String(originalState.valor || '0')) || 0,
                tipo: String(originalState.tipo || ''),
                eh_cartao_credito: Boolean(originalState.eh_cartao_credito),
                cartao_credito_nome: String(originalState.cartao_credito_nome || ''),
                data_vencimento: String(originalState.data_vencimento || ''),
                periodicidade: String(originalState.periodicidade || ''),
                recorrente: Boolean(originalState.recorrente),
            };

            const houveAlteracao = estadoAtual.descricao !== estadoOriginal.descricao
                || estadoAtual.categoria_despesa_id !== estadoOriginal.categoria_despesa_id
                || Math.abs(estadoAtual.valor - estadoOriginal.valor) > 0.001
                || estadoAtual.tipo !== estadoOriginal.tipo
                || estadoAtual.eh_cartao_credito !== estadoOriginal.eh_cartao_credito
                || estadoAtual.cartao_credito_nome !== estadoOriginal.cartao_credito_nome
                || estadoAtual.data_vencimento !== estadoOriginal.data_vencimento
                || estadoAtual.periodicidade !== estadoOriginal.periodicidade
                || estadoAtual.recorrente !== estadoOriginal.recorrente;

            const despesaRecorrente = estadoOriginal.recorrente || estadoAtual.recorrente;

            if (despesaRecorrente && houveAlteracao) {
                document.getElementById('modalConfirmarAlteracaoValor').classList.add('open');
                return;
            }

            document.getElementById('ed_alterar_futuras').value = '0';
            await submitEditDespesa();
        });

        document.getElementById('btnFecharConfirmacaoValor').addEventListener('click', () => {
            document.getElementById('modalConfirmarAlteracaoValor').classList.remove('open');
        });

        document.getElementById('btnAlterarSomenteEsta').addEventListener('click', async () => {
            document.getElementById('modalConfirmarAlteracaoValor').classList.remove('open');
            document.getElementById('ed_alterar_futuras').value = '0';
            await submitEditDespesa();
        });

        document.getElementById('btnAlterarTodasFuturas').addEventListener('click', async () => {
            document.getElementById('modalConfirmarAlteracaoValor').classList.remove('open');
            document.getElementById('ed_alterar_futuras').value = '1';
            await submitEditDespesa();
        });

        document.getElementById('btnFecharConfirmacaoExclusao').addEventListener('click', () => {
            fecharModalExclusaoRecorrencia();
        });

        document.getElementById('btnExcluirSomenteEstaDespesa').addEventListener('click', async () => {
            const form = formPendenteExclusaoRecorrencia;

            fecharModalExclusaoRecorrencia();

            if (!form) {
                return;
            }

            await enviarExclusaoDespesa(form, false);
        });

        document.getElementById('btnExcluirEstaEDemais').addEventListener('click', async () => {
            const form = formPendenteExclusaoRecorrencia;

            fecharModalExclusaoRecorrencia();

            if (!form) {
                return;
            }

            await enviarExclusaoDespesa(form, true);
        });

        const shouldOpenReceitaModal = app?.dataset.openReceita === '1';
        const shouldOpenDespesaModal = app?.dataset.openDespesa === '1';

        dEhCartaoCredito?.addEventListener('change', () => atualizarCamposCartaoCredito('d'));
        edEhCartaoCredito?.addEventListener('change', () => atualizarCamposCartaoCredito('ed'));

        formCreateDespesa?.addEventListener('submit', () => {
            atualizarCamposCartaoCredito('d');
        });

        atualizarCamposCartaoCredito('d');
        atualizarCamposCartaoCredito('ed');

        if (shouldOpenReceitaModal) {
            document.getElementById('modalReceita').classList.add('open');
        }

        if (shouldOpenDespesaModal) {
            document.getElementById('modalDespesa').classList.add('open');
        }

        document.querySelectorAll('[data-money]').forEach(setupMoneyMask);

        document.querySelectorAll('#modalReceita form, #modalDespesa form').forEach(function(form) {
            form.addEventListener('submit', function() {
                this.querySelectorAll('[data-money]').forEach(function(input) {
                    if (input.value !== '') {
                        input.value = input.value.replace(/\./g, '').replace(',', '.');
                    }
                });
            });
        });
        });
