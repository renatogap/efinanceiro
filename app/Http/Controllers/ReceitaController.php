<?php

namespace App\Http\Controllers;

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
        [$mes, $ano] = $this->normalizarPeriodo($request);
        $dados = $this->obterDadosPorPeriodo($mes, $ano);

        return view('financeiro.index', [
            ...$dados,
            'mesSelecionado' => $mes,
            'anoSelecionado' => $ano,
        ]);
    }

    /**
     * Return movement section data through AJAX.
     */
    public function movimentacoes(Request $request)
    {
        [$mes, $ano] = $this->normalizarPeriodo($request);
        $dados = $this->obterDadosPorPeriodo($mes, $ano);
        $periodoLabel = sprintf('%02d/%04d', $mes, $ano);

        return response()->json([
            'html' => view('financeiro._movimentacoes', [
                ...$dados,
                'periodoLabel' => $periodoLabel,
            ])->render(),
            'mes' => $mes,
            'ano' => $ano,
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
        $mes = (int) $request->input('mes', now()->month);
        $ano = (int) $request->input('ano', now()->year);

        if ($mes < 1 || $mes > 12) {
            $mes = now()->month;
        }

        if ($ano < 2000 || $ano > 2100) {
            $ano = now()->year;
        }

        return [$mes, $ano];
    }

    private function obterDadosPorPeriodo(int $mes, int $ano): array
    {
        $this->sincronizarRecorrenciasMensaisFixas($mes, $ano);

        $receitas = Receita::query()
            ->whereYear('data_credito', $ano)
            ->whereMonth('data_credito', $mes)
            ->latest('data_credito')
            ->latest()
            ->get();

        $despesas = Despesa::query()
            ->whereYear('data_vencimento', $ano)
            ->whereMonth('data_vencimento', $mes)
            ->latest('data_vencimento')
            ->latest()
            ->get();

        $totalReceitas = $receitas->sum('valor');
        $totalDespesas = $despesas->sum('valor');
        $quantidadeDespesas = $despesas->count();
        $quantidadeDespesasPagas = $despesas->where('pago', true)->count();
        $percentualDespesasPagas = $quantidadeDespesas > 0
            ? round(($quantidadeDespesasPagas / $quantidadeDespesas) * 100, 1)
            : 0;

        return [
            'receitas' => $receitas,
            'despesas' => $despesas,
            'totalReceitas' => $totalReceitas,
            'totalDespesas' => $totalDespesas,
            'saldo' => $totalReceitas - $totalDespesas,
            'percentualDespesasPagas' => $percentualDespesasPagas,
        ];
    }

    private function sincronizarRecorrenciasMensaisFixas(int $mes, int $ano): void
    {
        $limite = Carbon::create($ano, $mes, 1)->endOfMonth();

        $series = Despesa::query()
            ->where('tipo', 'fixa')
            ->where('recorrente', true)
            ->where('periodicidade', 'mensal')
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
