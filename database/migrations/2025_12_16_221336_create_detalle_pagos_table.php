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
        Schema::create('detalle_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas');
            $table->foreignId('tipo_pago_id')->constrained('tipo_pagos');
            $table->decimal('monto', 10, 2);
            $table->string('transaccion_id_pagofacil')->nullable()->after('tipo_pago_id');
            $table->string('transaccion_uuid')->nullable()->after('transaccion_id_pagofacil');
            $table->text('qr_image')->nullable()->after('transaccion_uuid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_pagos');
    }
};
