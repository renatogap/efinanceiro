<?php

namespace App\Http\Controllers;

use App\Models\CategoriaDespesa;
use App\Models\Despesa;
use App\Models\Receita;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReceitaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        [$inicio, $fim] = $this->normalizarPeriodo($request);
        $dados = $this->obterDadosPorPeriodo($inicio, $fim);
        $periodoLabel = $inicio->format('d/m/Y').' - '.$fim->format('d/m/Y');

        return view('financeiro.index', [
            ...$dados,
            'categoriasDespesa' => CategoriaDespesa::query()->orderBy('nome')->get(),
            'categoriasDespesaPayload' => CategoriaDespesa::query()
                ->withCount('despesas')
                ->orderBy('nome')
                ->get()
                ->map(fn (CategoriaDespesa $categoria) => [
                    'id' => $categoria->id,
                    'nome' => $categoria->nome,
                    'despesas_count' => $categoria->despesas_count,
                    'is_protected' => mb_strtolower(trim($categoria->nome), 'UTF-8') === 'financeiro',
                ])
                ->values(),
            'inicioSelecionado' => $inicio->toDateString(),
            'fimSelecionado' => $fim->toDateString(),
            'periodoLabel' => $periodoLabel,
        ]);
    }

    /**
     * Return movement section data through AJAX.
     */
    public function movimentacoes(Request $request)
    {
        [$inicio, $fim] = $this->normalizarPeriodo($request);
        $dados = $this->obterDadosPorPeriodo($inicio, $fim);
        $periodoLabel = $inicio->format('d/m/Y').' - '.$fim->format('d/m/Y');

        return response()->json([
            'html' => view('financeiro._movimentacoes', [
                ...$dados,
                'periodoLabel' => $periodoLabel,
            ])->render(),
            'inicio' => $inicio->toDateString(),
            'fim' => $fim->toDateString(),
            'totalReceitas' => (float) $dados['totalReceitas'],
            'totalDespesas' => (float) $dados['totalDespesas'],
            'saldo' => (float) $dados['saldo'],
        ]);
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
            'valor' => ['required', 'numeric', 'min:0.01'],
            'data_credito' => ['required', 'date'],
            'fonte' => ['nullable', 'string', 'max:80'],
        ]);

        Receita::create($data);

        return redirect()->back()->with('status', 'Receita cadastrada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Receita $receita)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Receita $receita)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Receita $receita)
    {
        $data = $request->validate([
            'descricao' => ['required', 'string', 'max:120'],
            'valor' => ['required', 'numeric', 'min:0.01'],
            'data_credito' => ['required', 'date'],
            'fonte' => ['nullable', 'string', 'max:80'],
        ]);

        $receita->update($data);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Receita atualizada com sucesso.',
            ]);
        }

        return redirect()->back()->with('status', 'Receita atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Receita $receita)
    {
        $receita->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Receita excluida com sucesso.',
            ]);
        }

        return redirect()->back()->with('status', 'Receita excluida com sucesso.');
    }

    private function normalizarPeriodo(Request $request): array
    {
        $inicioInput = $request->input('inicio');
        $fimInput = $request->input('fim');
        $agora = now();
        $defaultFim = Carbon::create($agora->year, $agora->month, 22);
        $defaultInicio = $defaultFim->copy()->subMonthNoOverflow()->addDay();

        try {
            $inicio = $inicioInput
                ? Carbon::createFromFormat('Y-m-d', $inicioInput)
                : $defaultInicio->copy();
        } catch (\Throwable $e) {
            $inicio = $defaultInicio->copy();
        }

        try {
            $fim = $fimInput
                ? Carbon::createFromFormat('Y-m-d', $fimInput)
                : $defaultFim->copy();
        } catch (\Throwable $e) {
            $fim = $defaultFim->copy();
        }

        if ($inicio->gt($fim)) {
            [$inicio, $fim] = [$fim, $inicio];
        }

        return [$inicio->startOfDay(), $fim->endOfDay()];
    }

    private function obterDadosPorPeriodo(Carbon $inicio, Carbon $fim): array
    {
        $this->sincronizarRecorrenciasMensaisFixas($fim->copy()->endOfMonth());

        $receitas = Receita::query()
            ->whereBetween('data_credito', [$inicio->toDateString(), $fim->toDateString()])
            ->latest('data_credito')
            ->latest()
            ->get();

        $despesas = Despesa::query()
            ->with('categoria')
            ->whereBetween('data_vencimento', [$inicio->toDateString(), $fim->toDateString()])
            ->latest('data_vencimento')
            ->latest()
            ->get();

        $despesasFinanceiras = $despesas->where('eh_cartao_credito', false);

        $recorrenciasUids = $despesas
            ->where('recorrente', true)
            ->pluck('recorrencia_uid')
            ->filter()
            ->unique()
            ->values();

        $ultimasDatasPorRecorrencia = $recorrenciasUids->isEmpty()
            ? collect()
            : Despesa::query()
                ->selectRaw('recorrencia_uid, MAX(data_vencimento) as ultima_data_vencimento')
                ->whereIn('recorrencia_uid', $recorrenciasUids)
                ->groupBy('recorrencia_uid')
                ->pluck('ultima_data_vencimento', 'recorrencia_uid');

        $despesas->each(function (Despesa $despesa) use ($ultimasDatasPorRecorrencia) {
            $ultimaData = $ultimasDatasPorRecorrencia->get($despesa->recorrencia_uid);

            $despesa->setAttribute(
                'has_futuras_recorrencias',
                $despesa->recorrente
                    && ! empty($despesa->recorrencia_uid)
                    && $ultimaData
                    && Carbon::parse($ultimaData)->gt($despesa->data_vencimento)
            );
        });

        $totalReceitas = $receitas->sum('valor');
        $totalDespesas = $despesasFinanceiras->sum('valor');
        $despesasPorCategoria = $despesasFinanceiras
            ->groupBy(fn ($despesa) => $despesa->categoria?->nome ?: 'Sem categoria')
            ->map(fn ($itens) => (float) $itens->sum('valor'))
            ->sortDesc()
            ->all();
        $quantidadeDespesas = $despesasFinanceiras->count();
        $quantidadeDespesasPagas = $despesasFinanceiras->where('pago', true)->count();
        $percentualDespesasPagas = $quantidadeDespesas > 0
            ? round(($quantidadeDespesasPagas / $quantidadeDespesas) * 100, 1)
            : 0;

        return [
            'receitas' => $receitas,
            'despesas' => $despesas,
            'totalReceitas' => $totalReceitas,
            'totalDespesas' => $totalDespesas,
            'despesasPorCategoria' => $despesasPorCategoria,
            'saldo' => $totalReceitas - $totalDespesas,
            'percentualDespesasPagas' => $percentualDespesasPagas,
        ];
    }

    private function sincronizarRecorrenciasMensaisFixas(Carbon $limite): void
    {
        $series = Despesa::query()
            ->where('recorrente', true)
            ->whereNotNull('recorrencia_uid')
            ->distinct()
            ->pluck('recorrencia_uid');

        foreach ($series as $uid) {
            $ultimo = Despesa::query()
                ->where('recorrencia_uid', $uid)
                ->orderByDesc('data_vencimento')
                ->first();

            while ($ultimo && $ultimo->data_vencimento->copy()->addMonthNoOverflow()->lte($limite)) {
                $proximaData = $ultimo->data_vencimento->copy()->addMonthNoOverflow();

                $jaExiste = Despesa::query()
                    ->where('recorrencia_uid', $uid)
                    ->whereYear('data_vencimento', $proximaData->year)
                    ->whereMonth('data_vencimento', $proximaData->month)
                    ->exists();

                if ($jaExiste) {
                    $ultimo = Despesa::query()
                        ->where('recorrencia_uid', $uid)
                        ->whereYear('data_vencimento', $proximaData->year)
                        ->whereMonth('data_vencimento', $proximaData->month)
                        ->orderByDesc('data_vencimento')
                        ->first();
                    continue;
                }

                $ultimo = Despesa::create([
                    'descricao' => $ultimo->descricao,
                    'valor' => $ultimo->valor,
                    'tipo' => $ultimo->tipo,
                    'recorrente' => $ultimo->recorrente,
                    'periodicidade' => $ultimo->periodicidade,
                    'recorrencia_uid' => $ultimo->recorrencia_uid,
                    'data_vencimento' => $proximaData,
                    'pago' => false,
                ]);
            }
        }
    }
}
