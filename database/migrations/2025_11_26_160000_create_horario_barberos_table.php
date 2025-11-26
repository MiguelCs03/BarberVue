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
        Schema::create('horario_barberos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barbero_id')->constrained('barberos')->onDelete('cascade');
            $table->enum('dia_semana', ['1', '2', '3', '4', '5', '6', '7']); // 1=Lunes, 7=Domingo
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->timestamps();
            
            // Índice para mejorar consultas
            $table->index(['barbero_id', 'dia_semana']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horario_barberos');
    }
};
