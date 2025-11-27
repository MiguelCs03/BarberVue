#!/usr/bin/env php
<?php

/**
 * Script de verificación para servidor con subcarpetas
 * Verifica que todas las URLs y configuraciones sean correctas
 */

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║    VERIFICACIÓN DE CONFIGURACIÓN PARA SUBCARPETAS             ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Cargar .env
$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) {
    echo "❌ Archivo .env no encontrado\n";
    echo "   Copia .env.example a .env y configúralo\n";
    exit(1);
}

$env = parse_ini_file($envFile);

echo "📋 Verificando configuración de URLs...\n\n";

// Verificar APP_URL
$appUrl = $env['APP_URL'] ?? '';
$expectedAppUrl = 'https://www.tecnoweb.org.bo/inf513/grupo14sc/proyecto2/BarberVue';

echo "1. APP_URL:\n";
echo "   Configurado: " . ($appUrl ?: '(vacío)') . "\n";
echo "   Esperado:    " . $expectedAppUrl . "\n";

if ($appUrl === $expectedAppUrl) {
    echo "   ✅ Correcto\n\n";
} else {
    echo "   ❌ INCORRECTO - Actualiza APP_URL en .env\n\n";
}

// Verificar PAGOFACIL_CALLBACK_URL
$callbackUrl = $env['PAGOFACIL_CALLBACK_URL'] ?? '';
$expectedCallback = 'https://www.tecnoweb.org.bo/inf513/grupo14sc/proyecto2/BarberVue/api/citas/callback-pagofacil';

echo "2. PAGOFACIL_CALLBACK_URL:\n";
echo "   Configurado: " . ($callbackUrl ?: '(vacío)') . "\n";
echo "   Esperado:    " . $expectedCallback . "\n";

if ($callbackUrl === $expectedCallback) {
    echo "   ✅ Correcto\n\n";
} else {
    echo "   ❌ INCORRECTO - Actualiza PAGOFACIL_CALLBACK_URL en .env\n\n";
}

// Verificar ASSET_URL (opcional pero recomendado)
$assetUrl = $env['ASSET_URL'] ?? '';
echo "3. ASSET_URL (opcional):\n";
echo "   Configurado: " . ($assetUrl ?: '(no configurado)') . "\n";
echo "   Recomendado: " . $expectedAppUrl . "\n";

if ($assetUrl === $expectedAppUrl) {
    echo "   ✅ Correcto\n\n";
} elseif (empty($assetUrl)) {
    echo "   ⚠️  No configurado (opcional, pero recomendado)\n\n";
} else {
    echo "   ❌ INCORRECTO\n\n";
}

// Verificar tokens de PagoFácil
echo "4. Tokens de PagoFácil:\n";
$serviceToken = $env['PAGOFACIL_SERVICE_TOKEN'] ?? '';
$secretToken = $env['PAGOFACIL_SECRET_TOKEN'] ?? '';

if (empty($serviceToken) || empty($secretToken)) {
    echo "   ❌ Tokens no configurados\n";
    echo "   Necesitas configurar:\n";
    echo "   - PAGOFACIL_SERVICE_TOKEN\n";
    echo "   - PAGOFACIL_SECRET_TOKEN\n\n";
} else {
    echo "   ✅ Tokens configurados\n";
    echo "   Service Token: " . substr($serviceToken, 0, 20) . "...\n";
    echo "   Secret Token:  " . substr($secretToken, 0, 10) . "...\n\n";
}

// Verificar archivos importantes
echo "📁 Verificando archivos importantes...\n\n";

$files = [
    'public/.htaccess' => 'Archivo de reescritura de URLs',
    'public/index.php' => 'Punto de entrada de Laravel',
    'bootstrap/cache' => 'Directorio de caché',
    'storage/logs' => 'Directorio de logs',
];

foreach ($files as $file => $description) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        echo "   ✅ $description ($file)\n";
    } else {
        echo "   ❌ $description ($file) - NO ENCONTRADO\n";
    }
}

echo "\n";

// Verificar permisos
echo "🔐 Verificando permisos...\n\n";

$dirs = [
    'storage',
    'bootstrap/cache',
];

foreach ($dirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (is_writable($path)) {
        echo "   ✅ $dir es escribible\n";
    } else {
        echo "   ❌ $dir NO es escribible - Ejecuta: chmod -R 775 $dir\n";
    }
}

echo "\n";

// Comandos recomendados
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                    COMANDOS RECOMENDADOS                       ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

if ($appUrl !== $expectedAppUrl || $callbackUrl !== $expectedCallback) {
    echo "⚠️  Actualiza tu archivo .env:\n\n";
    echo "APP_URL=$expectedAppUrl\n";
    echo "PAGOFACIL_CALLBACK_URL=$expectedCallback\n";
    echo "ASSET_URL=$expectedAppUrl\n\n";
    echo "Luego ejecuta:\n";
    echo "php artisan config:clear\n\n";
}

echo "📝 Para desplegar en producción:\n\n";
echo "1. composer install --optimize-autoloader --no-dev\n";
echo "2. chmod -R 775 storage bootstrap/cache\n";
echo "3. php artisan config:cache\n";
echo "4. php artisan route:cache\n";
echo "5. php artisan view:cache\n";
echo "6. npm install && npm run build\n\n";

echo "🧪 Para probar el webhook:\n\n";
echo "curl -X POST $expectedCallback \\\n";
echo "  -H \"Content-Type: application/json\" \\\n";
echo "  -d '{\"PedidoID\":\"test\",\"Estado\":\"Pagado\"}'\n\n";

echo "✅ Verificación completada!\n";
