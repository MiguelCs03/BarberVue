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
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('barbero_id')->nullable()->constrained('barberos');
            $table->foreignId('tipo_pago_id')->constrained('tipo_pagos');
            $table->dateTime('fecha');
            $table->string('estado',50)->default('pendiente');
            $table->decimal('pago_inicial', 10, 2)->default(0);
            $table->decimal('monto_total', 10, 2)->default(0);
            $table->decimal('porcentaje_cita', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
