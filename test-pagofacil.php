#!/usr/bin/env php
<?php

/**
 * Script de prueba para verificar la conexión con PagoFácil
 * 
 * Uso: php test-pagofacil.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\PagoFacilService;
use Illuminate\Support\Str;

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║         PRUEBA DE CONEXIÓN CON PAGOFÁCIL                      ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Verificar configuración
echo "📋 Verificando configuración...\n";
$config = config('services.pagofacil');

if (empty($config['service_token']) || empty($config['secret_token'])) {
    echo "❌ ERROR: Tokens no configurados en .env\n";
    echo "   Por favor configura:\n";
    echo "   - PAGOFACIL_SERVICE_TOKEN\n";
    echo "   - PAGOFACIL_SECRET_TOKEN\n";
    exit(1);
}

echo "✅ Base URL: " . $config['base_url'] . "\n";
echo "✅ Callback URL: " . $config['callback_url'] . "\n";
echo "✅ Client Code: " . $config['client_code'] . "\n";
echo "✅ Service Token: " . substr($config['service_token'], 0, 20) . "...\n";
echo "✅ Secret Token: " . substr($config['secret_token'], 0, 10) . "...\n\n";

// Prueba 1: Autenticación
echo "🔐 Prueba 1: Autenticación con PagoFácil...\n";
try {
    $service = new PagoFacilService();
    
    // Limpiar caché para forzar nueva autenticación
    Cache::forget('pagofacil_access_token');
    
    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('authenticate');
    $method->setAccessible(true);
    
    $token = $method->invoke($service);
    
    echo "✅ Autenticación exitosa!\n";
    echo "   Token: " . substr($token, 0, 50) . "...\n\n";
    
} catch (Exception $e) {
    echo "❌ Error en autenticación: " . $e->getMessage() . "\n";
    echo "   Revisa los logs en storage/logs/laravel.log\n";
    exit(1);
}

// Prueba 2: Generación de QR
echo "🎫 Prueba 2: Generación de QR de prueba...\n";
try {
    $uuid = (string) Str::uuid();
    $testAmount = 0.02; // Monto mínimo de prueba,  monto generado para pago facil
    
    $clientData = [
        'name' => 'Cliente de Prueba',
        'documentId' => '123456',
        'phoneNumber' => '75540850',
        'email' => 'prueba@barbervue.com',
    ];
    
    $qrData = $service->generarQr(
        $uuid,
        $testAmount,
        $clientData,
        'Prueba de Integración - BarberVue'
    );
    
    echo "✅ QR generado exitosamente!\n";
    echo "   Transaction ID: " . $qrData['transactionId'] . "\n";
    echo "   UUID: " . $uuid . "\n";
    echo "   QR Image: " . substr($qrData['qrImage'], 0, 50) . "... (Base64)\n\n";
    
    // Guardar QR en archivo para inspección
    $qrImagePath = storage_path('app/test-qr.txt');
    file_put_contents($qrImagePath, $qrData['qrImage']);
    echo "   QR guardado en: " . $qrImagePath . "\n\n";
    
} catch (Exception $e) {
    echo "❌ Error al generar QR: " . $e->getMessage() . "\n";
    echo "   Revisa los logs en storage/logs/laravel.log\n";
    exit(1);
}

// Resumen
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                    ✅ TODAS LAS PRUEBAS PASARON                ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "📝 Próximos pasos:\n";
echo "   1. Revisa los logs: tail -f storage/logs/laravel.log\n";
echo "   2. Prueba crear una cita desde el frontend\n";
echo "   3. Verifica que se genere el QR correctamente\n";
echo "   4. Simula un pago con el webhook\n\n";

echo "🎉 ¡La integración con PagoFácil está funcionando correctamente!\n";
