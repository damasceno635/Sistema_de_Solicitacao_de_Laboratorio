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
        Schema::table('users', function (Blueprint $table) {
            // Adiciona a coluna 'role' (admin, curso, professor)
            $table->string('role')->default('professor')->after('email');

            // Campos Adicionais: Curso (para Coordenador de Curso e Professor)
            $table->string('course')->nullable()->after('role');

            // Campos Adicionais: Disciplina e Período (para Professor)
            $table->string('discipline')->nullable()->after('course');
            $table->string('period')->nullable()->after('discipline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'course', 'discipline', 'period']);
        });
    }
};
