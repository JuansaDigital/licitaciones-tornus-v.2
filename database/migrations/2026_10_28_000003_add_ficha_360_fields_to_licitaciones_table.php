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
                    if (!Schema::connection($conn)->hasColumn('licitaciones', 'duracion')) {
                        $table->string('duracion')->nullable();
                    }
                    if (!Schema::connection($conn)->hasColumn('licitaciones', 'unidad_duracion')) {
                        $table->string('unidad_duracion')->nullable();
                    }
                    if (!Schema::connection($conn)->hasColumn('licitaciones', 'codigo_unspsc')) {
                        $table->string('codigo_unspsc')->nullable()->index();
                    }
                    if (!Schema::connection($conn)->hasColumn('licitaciones', 'tipo_contrato')) {
                        $table->string('tipo_contrato')->nullable();
                    }
                    if (!Schema::connection($conn)->hasColumn('licitaciones', 'orden_entidad')) {
                        $table->string('orden_entidad')->nullable();
                    }
                    if (!Schema::connection($conn)->hasColumn('licitaciones', 'nit_entidad')) {
                        $table->string('nit_entidad')->nullable();
                    }
                    if (!Schema::connection($conn)->hasColumn('licitaciones', 'fecha_apertura_ofertas')) {
                        $table->string('fecha_apertura_ofertas')->nullable();
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
                    $cols = [
                        'duracion',
                        'unidad_duracion',
                        'codigo_unspsc',
                        'tipo_contrato',
                        'orden_entidad',
                        'nit_entidad',
                        'fecha_apertura_ofertas'
                    ];
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
