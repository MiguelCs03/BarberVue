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
        Schema::table('citas', function (Blueprint $table) {
            $table->string('transaccion_id_pagofacil')->nullable()->after('tipo_pago_id');
            $table->string('transaccion_uuid')->nullable()->after('transaccion_id_pagofacil');
            $table->text('qr_image')->nullable()->after('transaccion_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropColumn(['transaccion_id_pagofacil', 'transaccion_uuid', 'qr_image']);
        });
    }
};
