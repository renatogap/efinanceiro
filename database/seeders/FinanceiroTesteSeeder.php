<?php

namespace Database\Seeders;

use App\Models\CategoriaDespesa;
use App\Models\Despesa;
use App\Models\Receita;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FinanceiroTesteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoriasIds = CategoriaDespesa::query()->pluck('id')->all();

        for ($m = 0; $m < 12; $m++) {
            $base = Carbon::now()->startOfMonth()->subMonths($m);

            for ($i = 1; $i <= 3; $i++) {
                Receita::create([
                    'descricao' => 'Receita Teste '.($m + 1).'-'.$i,
                    'valor' => rand(1800, 5200) + ($i * 37),
                    'data_credito' => $base->copy()->day(min(27, 4 + ($i * 7))),
                    'fonte' => ['Salario', 'Freela', 'Bonus'][($i - 1) % 3],
                ]);
            }

            for ($j = 1; $j <= 4; $j++) {
                $tipo = $j <= 2 ? 'fixa' : 'variavel';
                $recorrente = $j <= 2;

                Despesa::create([
                    'descricao' => 'Despesa Teste '.($m + 1).'-'.$j,
                    'categoria_despesa_id' => $categoriasIds[array_rand($categoriasIds)],
                    'valor' => rand(200, 2400) + ($j * 19),
                    'tipo' => $tipo,
                    'recorrente' => $recorrente,
                    'periodicidade' => $recorrente ? 'mensal' : null,
                    'data_vencimento' => $base->copy()->day(min(28, 3 + ($j * 6))),
                ]);
            }
        }
    }
}
