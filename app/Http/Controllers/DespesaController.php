<?php

namespace App\Http\Controllers;

use App\Models\Despesa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DespesaController extends Controller
{
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
            'valor' => ['required', 'numeric', 'min:0.01'],
            'tipo' => ['required', 'in:fixa,variavel'],
            'recorrente' => ['nullable', 'boolean'],
            'periodicidade' => ['nullable', 'in:semanal,mensal,anual'],
            'data_vencimento' => ['required', 'date'],
        ]);

        $data['recorrente'] = $request->boolean('recorrente');
        $data['recorrencia_uid'] = null;

        if (! $data['recorrente']) {
            $data['periodicidade'] = null;
        }

        if ($this->isRecorrenciaMensalFixaData($data)) {
            $data['recorrencia_uid'] = (string) Str::uuid();
        }

        $despesa = Despesa::create($data);

        if ($this->isRecorrenciaMensalFixa($despesa)) {
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
        $valorAnterior = (float) $despesa->valor;

        $data = $request->validate([
            'descricao' => ['required', 'string', 'max:120'],
            'valor' => ['required', 'numeric', 'min:0.01'],
            'tipo' => ['required', 'in:fixa,variavel'],
            'recorrente' => ['nullable', 'boolean'],
            'periodicidade' => ['nullable', 'in:semanal,mensal,anual'],
            'data_vencimento' => ['required', 'date'],
        ]);

        $data['recorrente'] = $request->boolean('recorrente');

        if (! $data['recorrente']) {
            $data['periodicidade'] = null;
        }

        $data['recorrencia_uid'] = $despesa->recorrencia_uid;

        if ($this->isRecorrenciaMensalFixaData($data)) {
            $data['recorrencia_uid'] = $data['recorrencia_uid'] ?: (string) Str::uuid();
        }

        $despesa->update($data);

        if ($this->isRecorrenciaMensalFixa($despesa)) {
            if ((float) $despesa->valor !== $valorAnterior) {
                Despesa::query()
                    ->where('recorrencia_uid', $despesa->recorrencia_uid)
                    ->whereDate('data_vencimento', '>', $despesa->data_vencimento)
                    ->update(['valor' => $despesa->valor]);
            }

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
        $despesa->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Despesa excluida com sucesso.',
            ]);
        }

        return redirect()->back()->with('status', 'Despesa excluida com sucesso.');
    }

    /**
     * Mark expense as paid.
     */
    public function marcarComoPaga(Request $request, Despesa $despesa)
    {
        if (! $despesa->pago) {
            $despesa->update(['pago' => true]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Despesa marcada como paga.',
            ]);
        }

        return redirect()->back()->with('status', 'Despesa marcada como paga.');
    }

    private function isRecorrenciaMensalFixaData(array $data): bool
    {
        return ($data['tipo'] ?? null) === 'fixa'
            && ($data['recorrente'] ?? false)
            && ($data['periodicidade'] ?? null) === 'mensal';
    }

    private function isRecorrenciaMensalFixa(Despesa $despesa): bool
    {
        return $despesa->tipo === 'fixa'
            && $despesa->recorrente
            && $despesa->periodicidade === 'mensal'
            && ! empty($despesa->recorrencia_uid);
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
