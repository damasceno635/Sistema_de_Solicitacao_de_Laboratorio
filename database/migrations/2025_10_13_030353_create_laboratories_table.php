<?php

// database/migrations/YYYY_MM_DD_create_laboratories_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratories', function (Blueprint $table) {
            $table->id();
            
            // Campos obrigatórios
            $table->string('name')->unique(); // Nome
            $table->string('location'); // Localização
            $table->integer('capacity'); // Capacidade de Ocupação
            $table->text('materials_supplied'); // Materiais Fornecidos
            $table->boolean('is_available')->default(true); // Status (disponível/não)

            // Campo não obrigatório
            $table->text('observation')->nullable(); // Observação
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratories');
    }
};
