<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('despesas', function (Blueprint $table) {
            $table->string('recorrencia_uid', 36)->nullable()->after('periodicidade');
            $table->index('recorrencia_uid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('despesas', function (Blueprint $table) {
            $table->dropIndex(['recorrencia_uid']);
            $table->dropColumn('recorrencia_uid');
        });
    }
};
