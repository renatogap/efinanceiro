<?php

namespace App\Http\Controllers;

use App\Models\CategoriaDespesa;
use App\Models\Despesa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DespesaController extends Controller
{
    private const FORMAS_PAGAMENTO = [
        'cartao_credito',
        'cartao_debito',
        'pix',
        'dinheiro',
        'boleto',
        'transferencia',
    ];

    private const CARTOES_CREDITO = [
        'inter',
        'santander',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'descricao' => ['required', 'string', 'max:120'],
            'categoria_despesa_id' => ['required', 'exists:categorias_despesa,id'],
            'valor' => ['required', 'numeric', 'min:0.01'],
            'tipo' => ['required', 'in:fixa,variavel'],
            'eh_cartao_credito' => ['nullable', 'boolean'],
            'cartao_credito_nome' => ['nullable', 'in:'.implode(',', self::CARTOES_CREDITO), 'required_if:eh_cartao_credito,1'],
            'recorrente' => ['nullable', 'boolean'],
            'periodicidade' => ['nullable', 'in:semanal,mensal,anual'],
            'data_vencimento' => ['required', 'date'],
        ]);

        $data['eh_cartao_credito'] = $request->boolean('eh_cartao_credito');
        if (! $data['eh_cartao_credito']) {
            $data['cartao_credito_nome'] = null;
            $data['cartao_fatura_categoria_id'] = null;
        } else {
            $data['cartao_fatura_categoria_id'] = $this->resolverCategoriaFaturaId($data['cartao_credito_nome'] ?? null);

            if (! $data['cartao_fatura_categoria_id']) {
                throw ValidationException::withMessages([
                    'cartao_credito_nome' => 'Nao foi encontrada categoria de fatura para o cartao selecionado.',
                ]);
            }
        }

        $data['recorrente'] = $request->boolean('recorrente');
        $data['pago'] = false;
        $data['pago_cartao_credito'] = false;
        $data['forma_pagamento'] = null;
        $data['recorrencia_uid'] = null;

        if ($data['recorrente']) {
            $data['recorrencia_uid'] = (string) Str::uuid();
        }

        $despesa = Despesa::create($data);

        if ($despesa->recorrente && ! empty($despesa->recorrencia_uid)) {
            $this->garantirLancamentosRecorrentesAte(
                $despesa,
                now()->startOfMonth()->addMonthsNoOverflow(18)->endOfMonth()
            );
        }

        return redirect()->back()->with('status', 'Despesa cadastrada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Despesa $despesa)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Despesa $despesa)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Despesa $despesa)
    {
        $recorrenciaUidAnterior = $despesa->recorrencia_uid;

        $data = $request->validate([
            'descricao' => ['required', 'string', 'max:120'],
            'categoria_despesa_id' => ['required', 'exists:categorias_despesa,id'],
            'valor' => ['required', 'numeric', 'min:0.01'],
            'tipo' => ['required', 'in:fixa,variavel'],
            'eh_cartao_credito' => ['nullable', 'boolean'],
            'cartao_credito_nome' => ['nullable', 'in:'.implode(',', self::CARTOES_CREDITO), 'required_if:eh_cartao_credito,1'],
            'recorrente' => ['nullable', 'boolean'],
            'periodicidade' => ['nullable', 'in:semanal,mensal,anual'],
            'data_vencimento' => ['required', 'date'],
        ]);

        $data['eh_cartao_credito'] = $request->boolean('eh_cartao_credito');
        if (! $data['eh_cartao_credito']) {
            $data['cartao_credito_nome'] = null;
            $data['cartao_fatura_categoria_id'] = null;
        } else {
            $data['cartao_fatura_categoria_id'] = $this->resolverCategoriaFaturaId($data['cartao_credito_nome'] ?? null);

            if (! $data['cartao_fatura_categoria_id']) {
                throw ValidationException::withMessages([
                    'cartao_credito_nome' => 'Nao foi encontrada categoria de fatura para o cartao selecionado.',
                ]);
            }
        }

        $data['recorrente'] = $request->boolean('recorrente');
        $data['pago_cartao_credito'] = $despesa->forma_pagamento === 'cartao_credito';
        $data['forma_pagamento'] = $despesa->forma_pagamento;
        $data['pago'] = $despesa->pago;
        $data['recorrencia_uid'] = $despesa->recorrencia_uid;

        if ($data['recorrente']) {
            $data['recorrencia_uid'] = $data['recorrencia_uid'] ?: (string) Str::uuid();
        } else {
            $data['recorrencia_uid'] = null;
        }

        $despesa->update($data);

        if ($request->boolean('_alterar_futuras') && ! empty($recorrenciaUidAnterior)) {
            $diaVencimento = $despesa->data_vencimento->day;

            $futuras = Despesa::query()
                ->where('recorrencia_uid', $recorrenciaUidAnterior)
                ->whereDate('data_vencimento', '>', $despesa->data_vencimento)
                ->orderBy('data_vencimento')
                ->get();

            foreach ($futuras as $futura) {
                $ultimoDiaMes = Carbon::create($futura->data_vencimento->year, $futura->data_vencimento->month, 1)->endOfMonth()->day;
                $novaDataVencimento = Carbon::create(
                    $futura->data_vencimento->year,
                    $futura->data_vencimento->month,
                    min($diaVencimento, $ultimoDiaMes)
                );

                $futura->update([
                    'descricao' => $despesa->descricao,
                    'categoria_despesa_id' => $despesa->categoria_despesa_id,
                    'valor' => $despesa->valor,
                    'tipo' => $despesa->tipo,
                    'eh_cartao_credito' => $despesa->eh_cartao_credito,
                    'cartao_credito_nome' => $despesa->cartao_credito_nome,
                    'cartao_fatura_categoria_id' => $despesa->cartao_fatura_categoria_id,
                    'recorrente' => $despesa->recorrente,
                    'pago_cartao_credito' => $despesa->forma_pagamento === 'cartao_credito',
                    'periodicidade' => $despesa->periodicidade,
                    'recorrencia_uid' => $despesa->recorrencia_uid,
                    'data_vencimento' => $novaDataVencimento,
                    'pago' => $futura->pago,
                    'forma_pagamento' => $futura->forma_pagamento,
                ]);
            }
        }

        if ($despesa->recorrente && ! empty($despesa->recorrencia_uid)) {
            $this->garantirLancamentosRecorrentesAte(
                $despesa,
                now()->startOfMonth()->addMonthsNoOverflow(18)->endOfMonth()
            );
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Despesa atualizada com sucesso.',
            ]);
        }

        return redirect()->back()->with('status', 'Despesa atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Despesa $despesa)
    {
        $message = 'Despesa excluida com sucesso.';

        if (
            $request->boolean('_excluir_futuras')
            && $despesa->recorrente
            && ! empty($despesa->recorrencia_uid)
        ) {
            Despesa::query()
                ->where('recorrencia_uid', $despesa->recorrencia_uid)
                ->whereDate('data_vencimento', '>=', $despesa->data_vencimento)
                ->delete();

            $message = 'Despesa e recorrencias futuras excluidas com sucesso.';
        } else {
            $despesa->delete();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ]);
        }

        return redirect()->back()->with('status', $message);
    }

    /**
     * Mark expense as paid.
     */
    public function marcarComoPaga(Request $request, Despesa $despesa)
    {
        if ($request->boolean('_remover_pagamento')) {
            $despesa->update([
                'pago' => false,
                'forma_pagamento' => null,
                'pago_cartao_credito' => false,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Pagamento removido com sucesso.',
                ]);
            }

            return redirect()->back()->with('status', 'Pagamento removido com sucesso.');
        }

        $data = $request->validate([
            'forma_pagamento' => ['required', 'in:'.implode(',', self::FORMAS_PAGAMENTO)],
        ]);

        $despesa->update([
            'pago' => true,
            'forma_pagamento' => $data['forma_pagamento'],
            'pago_cartao_credito' => $data['forma_pagamento'] === 'cartao_credito',
        ]);

        $despesa->loadMissing('categoria');
        $cartaoFatura = $this->obterCartaoPorCategoriaFatura($despesa->categoria?->nome);

        if ($cartaoFatura) {
            Despesa::query()
                ->where('cartao_fatura_categoria_id', $despesa->categoria_despesa_id)
                ->where('id', '!=', $despesa->id)
                ->where('pago', false)
                ->update([
                    'pago' => true,
                    'forma_pagamento' => 'cartao_credito',
                    'pago_cartao_credito' => true,
                ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Despesa marcada como paga.',
            ]);
        }

        return redirect()->back()->with('status', 'Despesa marcada como paga.');
    }

    private function garantirLancamentosRecorrentesAte(Despesa $despesa, Carbon $ate): void
    {
        $ultimo = Despesa::query()
            ->where('recorrencia_uid', $despesa->recorrencia_uid)
            ->orderByDesc('data_vencimento')
            ->first();

        while ($ultimo && $ultimo->data_vencimento->copy()->addMonthNoOverflow()->lte($ate)) {
            $proximaData = $ultimo->data_vencimento->copy()->addMonthNoOverflow();

            $jaExiste = Despesa::query()
                ->where('recorrencia_uid', $despesa->recorrencia_uid)
                ->whereYear('data_vencimento', $proximaData->year)
                ->whereMonth('data_vencimento', $proximaData->month)
                ->exists();

            if ($jaExiste) {
                $ultimo = Despesa::query()
                    ->where('recorrencia_uid', $despesa->recorrencia_uid)
                    ->whereYear('data_vencimento', $proximaData->year)
                    ->whereMonth('data_vencimento', $proximaData->month)
                    ->orderByDesc('data_vencimento')
                    ->first();
                continue;
            }

            $ultimo = Despesa::create([
                'descricao' => $ultimo->descricao,
                'categoria_despesa_id' => $ultimo->categoria_despesa_id,
                'valor' => $ultimo->valor,
                'tipo' => $ultimo->tipo,
                'eh_cartao_credito' => $ultimo->eh_cartao_credito,
                'cartao_credito_nome' => $ultimo->cartao_credito_nome,
                'cartao_fatura_categoria_id' => $ultimo->cartao_fatura_categoria_id,
                'recorrente' => $ultimo->recorrente,
                'periodicidade' => $ultimo->periodicidade,
                'recorrencia_uid' => $ultimo->recorrencia_uid,
                'data_vencimento' => $proximaData,
                'pago' => false,
                'pago_cartao_credito' => false,
                'forma_pagamento' => null,
            ]);
        }
    }

    private function resolverCategoriaFaturaId(?string $cartao): ?int
    {
        if (! $cartao) {
            return null;
        }

        $cartao = mb_strtolower(trim($cartao), 'UTF-8');
        $alvo = $cartao === 'inter'
            ? 'cartao de credito - inter'
            : 'cartao de credito - santander';

        return CategoriaDespesa::query()
            ->get()
            ->first(function (CategoriaDespesa $categoria) use ($alvo) {
                return $this->normalizarTexto($categoria->nome) === $alvo;
            })
            ?->id;
    }

    private function obterCartaoPorCategoriaFatura(?string $nomeCategoria): ?string
    {
        $normalizado = $this->normalizarTexto($nomeCategoria);

        if ($normalizado === 'cartao de credito - inter') {
            return 'inter';
        }

        if ($normalizado === 'cartao de credito - santander') {
            return 'santander';
        }

        return null;
    }

    private function normalizarTexto(?string $texto): string
    {
        $texto = mb_strtolower(trim((string) $texto), 'UTF-8');
        $semAcentos = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);

        return preg_replace('/\s+/', ' ', $semAcentos ?: $texto) ?: '';
    }
}
