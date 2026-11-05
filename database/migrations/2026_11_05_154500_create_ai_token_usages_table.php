<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_token_usages', function (Blueprint $table) {
            $table->id();
            $table->string('model', 50)->default('gemini-3.8-flash');
            $table->string('operation', 50)->default('evaluacion_afinidad');
            $table->integer('prompt_tokens')->default(0);
            $table->integer('candidate_tokens')->default(0);
            $table->integer('total_tokens')->default(0);
            $table->decimal('cost_usd', 10, 6)->default(0.0);
            $table->unsignedBigInteger('licitacion_id')->nullable();
            $table->string('market', 10)->default('CO');
            $table->date('date')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_token_usages');
    }
};
