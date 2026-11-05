<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notificar_email')->default(true)->after('activo');
            $table->string('notificar_criterio', 40)->default('todas')->after('notificar_email'); // todas, afinidad_alta, prioritarias
            $table->string('notificar_mercado', 20)->default('TODOS')->after('notificar_criterio'); // TODOS, CO, PE
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['notificar_email', 'notificar_criterio', 'notificar_mercado']);
        });
    }
};

