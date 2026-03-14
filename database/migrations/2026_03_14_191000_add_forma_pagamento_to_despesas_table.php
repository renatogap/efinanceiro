<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('despesas', function (Blueprint $table) {
            $table->string('forma_pagamento', 30)->nullable()->after('pago_cartao_credito');
        });

        DB::table('despesas')
            ->where('pago_cartao_credito', true)
            ->update([
                'pago' => true,
                'forma_pagamento' => 'cartao_credito',
            ]);
    }

    public function down(): void
    {
        Schema::table('despesas', function (Blueprint $table) {
            $table->dropColumn('forma_pagamento');
        });
    }
};