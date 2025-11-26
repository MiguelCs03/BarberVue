<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ServicioBarbero;
use App\Models\Barbero;
use App\Models\Servicio;

try {
    // Verificar si ya existe la relación
    $exists = ServicioBarbero::where('barbero_id', 1)
        ->where('servicio_id', 1)
        ->exists();
    
    if ($exists) {
        echo "✓ La relación ya existe\n";
    } else {
        // Crear la relación
        ServicioBarbero::create([
            'barbero_id' => 1,
            'servicio_id' => 1,
        ]);
        echo "✓ Servicio asignado exitosamente al barbero\n";
    }
    
    // Verificar
    $barbero = Barbero::with('usuario', 'servicioBarberos.servicio')->find(1);
    echo "\nBarbero: " . $barbero->usuario->name . "\n";
    echo "Servicios asignados: " . $barbero->servicioBarberos->count() . "\n";
    
    foreach ($barbero->servicioBarberos as $sb) {
        echo "  - " . $sb->servicio->nombre . "\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
