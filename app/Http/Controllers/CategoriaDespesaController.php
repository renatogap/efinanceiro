<?php

namespace App\Http\Controllers;

use App\Models\CategoriaDespesa;
use App\Models\Despesa;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoriaDespesaController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => [
                'required',
                'string',
                'max:60',
                Rule::unique('categorias_despesa', 'nome'),
            ],
        ]);

        $categoria = CategoriaDespesa::create([
            'nome' => Str::title(trim($data['nome'])),
        ]);

        $categorias = $this->listarCategorias();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Categoria cadastrada com sucesso.',
                'categoria' => [
                    'id' => $categoria->id,
                    'nome' => $categoria->nome,
                ],
                'categorias' => $categorias,
            ]);
        }

        return redirect()->back()->with('status', 'Categoria cadastrada com sucesso.');
    }

    public function destroy(Request $request, CategoriaDespesa $categoriaDespesa)
    {
        if ($this->isCategoriaProtegida($categoriaDespesa)) {
            return response()->json([
                'message' => 'A categoria Financeiro nao pode ser excluida.',
            ], 422);
        }

        $quantidadeDespesas = $categoriaDespesa->despesas()->count();

        if ($quantidadeDespesas > 0) {
            $data = $request->validate([
                'categoria_destino_id' => [
                    'required',
                    'exists:categorias_despesa,id',
                    Rule::notIn([$categoriaDespesa->id]),
                ],
            ]);

            Despesa::query()
                ->where('categoria_despesa_id', $categoriaDespesa->id)
                ->update(['categoria_despesa_id' => $data['categoria_destino_id']]);
        }

        $categoriaDespesa->delete();

        return response()->json([
            'message' => 'Categoria excluida com sucesso.',
            'categorias' => $this->listarCategorias(),
        ]);
    }

    private function listarCategorias()
    {
        return CategoriaDespesa::query()
            ->withCount('despesas')
            ->orderBy('nome')
            ->get()
            ->map(fn (CategoriaDespesa $categoria) => [
                'id' => $categoria->id,
                'nome' => $categoria->nome,
                'despesas_count' => $categoria->despesas_count,
                'is_protected' => $this->isCategoriaProtegida($categoria),
            ])
            ->values();
    }

    private function isCategoriaProtegida(CategoriaDespesa $categoria): bool
    {
        return Str::lower(trim($categoria->nome)) === 'financeiro';
    }
}