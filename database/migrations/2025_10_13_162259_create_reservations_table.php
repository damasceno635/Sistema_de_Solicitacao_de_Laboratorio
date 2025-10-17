<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            
            // Chaves Estrangeiras
            // Quem fez a reserva
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); 
            // Qual laboratório foi reservado
            $table->foreignId('laboratory_id')->constrained('laboratories')->onDelete('cascade'); 

            // Campos Obrigatórios para o Professor
            $table->dateTime('start_time'); // Data e Horário (Início)
            $table->dateTime('end_time')->nullable(); // Sugestão: bom ter um fim (opcional)
            $table->text('lesson_plan'); // Roteiro de Aula

            // Status (A ser ligado aos coordenadores mais tarde)
            $table->enum('status', ['pendente', 'em andamento', 'aprovada', 'rejeitada'])->default('pendente');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
