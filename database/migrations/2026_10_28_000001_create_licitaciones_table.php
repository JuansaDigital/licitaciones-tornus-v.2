<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licitaciones', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->index();
            $table->string('referencia')->nullable();
            $table->string('entidad')->nullable();
            $table->string('adjudicador')->nullable();
            $table->text('objeto')->nullable();
            $table->string('departamento')->nullable();
            $table->string('municipio')->nullable();
            $table->decimal('cuantia', 18, 2)->default(0);
            $table->string('moneda', 10)->default('COP');
            $table->string('tipo_proceso')->nullable();
            $table->string('categoria', 30)->default('serv');
            $table->string('estado', 50)->default('Abierto');
            $table->string('fase', 50)->nullable();
            $table->string('fecha_publicacion', 30)->nullable();
            $table->string('fecha_cierre', 30)->nullable();
            $table->text('url_proceso')->nullable();
            $table->integer('prov_unicos')->default(0);
            $table->string('fase2_fecha', 30)->nullable();
            $table->string('adenda_fecha', 30)->nullable();
            
            // Afinidad IA Gemini
            $table->integer('score')->nullable();
            $table->string('nivel', 20)->nullable();
            $table->text('justificacion')->nullable();
            $table->json('servicios_sugeridos')->nullable();
            $table->string('estrategia_postulacion', 50)->nullable();
            $table->text('puntos_clave')->nullable();
            $table->dateTime('evaluado_en')->nullable();

            // Cartera / Pipeline
            $table->boolean('en_cartera')->default(false)->index();
            $table->string('cartera_estado', 50)->default('Identificada');
            $table->text('notas')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licitaciones');
    }
};
