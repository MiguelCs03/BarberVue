<?php

namespace Database\Seeders;

use App\Models\Servicio;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServicioBarberoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔗 Iniciando vinculación de Servicios a Barberos...');

        // 1. Recuperamos los barberos a través de sus usuarios
        // Usamos 'with' para traer la relación del perfil de barbero y optimizar consultas
        //luego hay que cabiar a
        $userB1 = User::where('email', 'barbero1@barbershop.com')->with('barbero')->first();
        $userB2 = User::where('email', 'barbero2@barbershop.com')->with('barbero')->first();
        $userB3 = User::where('email', 'barbero3@barbershop.com')->with('barbero')->first();

        // Validamos que existan antes de continuar
        if (!$userB1 || !$userB2 || !$userB3) {
            $this->command->error('❌ Error: No se encontraron los usuarios barberos. Ejecuta primero BarberoSeeder.');
            return;
        }

        $barbero1 = $userB1->barbero;
        $barbero2 = $userB2->barbero;
        $barbero3 = $userB3->barbero;

        // 2. Recuperamos TODOS los servicios ordenados por ID para tener consistencia
        $todosLosServicios = Servicio::orderBy('id')->get();

        if ($todosLosServicios->count() < 8) {
            $this->command->error('❌ Error: Necesitas al menos 8 servicios. Ejecuta ServicioSeeder.');
            return;
        }

        // --- LÓGICA DE ASIGNACIÓN ---

        // GRUPO A: Servicios "Comunes" (Los primeros 5 de la lista)
        // Ejemplo: Corte, Afeitado, Corte+Barba, Coloración, Tratamiento
        $serviciosComunes = $todosLosServicios->take(5);

        // GRUPO B: Servicios "Exclusivos" (Los últimos 3 de la lista)
        // Usamos negativo en take para tomar desde el final
        // Ejemplo: Perfilado, Lavado, Masaje
        $serviciosExclusivos = $todosLosServicios->take(-3);


        // --- ASIGNACIÓN AL BARBERO 1 (5 Servicios) ---
        // Le damos todo el Grupo A (5 servicios)
        $this->asignarServicios($barbero1, $serviciosComunes, 'Barbero 1');

        // --- ASIGNACIÓN AL BARBERO 2 (4 Servicios) ---
        // Le damos casi todo el Grupo A, pero le quitamos el último (4 servicios)
        // Compartirá 4 servicios idénticos con el Barbero 1
        $serviciosBarbero2 = $serviciosComunes->take(4);
        $this->asignarServicios($barbero2, $serviciosBarbero2, 'Barbero 2');

        // --- ASIGNACIÓN AL BARBERO 3 (3 Servicios) ---
        // Le damos el Grupo B. Son servicios que NI el 1 NI el 2 tienen.
        $this->asignarServicios($barbero3, $serviciosExclusivos, 'Barbero 3');

        $this->command->info('✨ Vinculación completada exitosamente.');
    }

    /**
     * Asigna servicios usando el modelo intermedio (hasMany)
     * evitando duplicados con firstOrCreate.
     */
    private function asignarServicios($barbero, $servicios, $nombreLog)
    {

        $this->command->info("  👤 {$nombreLog} ({$barbero->usuario->name}):");
        
        $contador = 0;

        foreach ($servicios as $servicio) {
            // 1. Accedemos a la relación hasMany (servicioBarberos)
            // 2. firstOrCreate busca un registro con ese servicio_id DENTRO de los de este barbero
            // 3. Si no existe, lo crea. Laravel pone el 'barbero_id' automáticamente.
            $pivot = $barbero->servicioBarberos()->firstOrCreate(
                ['servicio_id' => $servicio->id], // Condición: ¿Existe este servicio para este barbero?
                [] // Valores extra al crear (vacío por ahora)
            );

            if ($pivot->wasRecentlyCreated) {
                $contador++;
            }
        }

        $this->command->info("     ✅ Procesado: {$contador} nuevos asignados (Total: {$servicios->count()})");
        
        // Listado visual para confirmar en consola
        foreach ($servicios as $servicio) {
            $this->command->line("      - {$servicio->nombre}");
        }
        $this->command->line("-------------------------------------------");
    }
}
