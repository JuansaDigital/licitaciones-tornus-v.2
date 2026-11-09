<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connections = ['colombia', 'peru'];

        foreach ($connections as $conn) {
            if (Schema::connection($conn)->hasTable('licitaciones')) {
                Schema::connection($conn)->table('licitaciones', function (Blueprint $table) use ($conn) {
                    if (!Schema::connection($conn)->hasColumn('licitaciones', 'filtro_descarte')) {
                        $table->string('filtro_descarte', 10)->nullable()->after('puntos_clave');
                    }
                    if (!Schema::connection($conn)->hasColumn('licitaciones', 'subpuntajes')) {
                        $table->json('subpuntajes')->nullable()->after('filtro_descarte');
                    }
                    if (!Schema::connection($conn)->hasColumn('licitaciones', 'etiqueta')) {
                        $table->string('etiqueta', 50)->nullable()->after('subpuntajes');
                    }
                    if (!Schema::connection($conn)->hasColumn('licitaciones', 'antecedente_tornus')) {
                        $table->string('antecedente_tornus')->nullable()->after('etiqueta');
                    }
                    if (!Schema::connection($conn)->hasColumn('licitaciones', 'componente_aliado')) {
                        $table->text('componente_aliado')->nullable()->after('antecedente_tornus');
                    }
                });
            }
        }
    }

    public function down(): void
    {
        $connections = ['colombia', 'peru'];

        foreach ($connections as $conn) {
            if (Schema::connection($conn)->hasTable('licitaciones')) {
                Schema::connection($conn)->table('licitaciones', function (Blueprint $table) use ($conn) {
                    $cols = ['filtro_descarte', 'subpuntajes', 'etiqueta', 'antecedente_tornus', 'componente_aliado'];
                    foreach ($cols as $col) {
                        if (Schema::connection($conn)->hasColumn('licitaciones', $col)) {
                            $table->dropColumn($col);
                        }
                    }
                });
            }
        }
    }
};

