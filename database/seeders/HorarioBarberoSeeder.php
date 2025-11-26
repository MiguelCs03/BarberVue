<?php

namespace Database\Seeders;

use App\Models\Barbero;
use App\Models\ExcepcionHorario;
use App\Models\HorarioBarbero;
use Illuminate\Database\Seeder;

class HorarioBarberoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los barberos
        $barberos = Barbero::all();

        if ($barberos->isEmpty()) {
            $this->command->warn('No hay barberos en la base de datos. Ejecuta primero el seeder de usuarios.');
            return;
        }

        // Para cada barbero, crear horarios de ejemplo
        foreach ($barberos as $barbero) {
            // Horario de Lunes a Viernes: 9:00 - 18:00
            for ($dia = 1; $dia <= 5; $dia++) {
                HorarioBarbero::create([
                    'barbero_id' => $barbero->id,
                    'dia_semana' => (string)$dia,
                    'hora_inicio' => '09:00:00',
                    'hora_fin' => '18:00:00',
                ]);
            }

            // Sábado: 10:00 - 14:00
            HorarioBarbero::create([
                'barbero_id' => $barbero->id,
                'dia_semana' => '6',
                'hora_inicio' => '10:00:00',
                'hora_fin' => '14:00:00',
            ]);

            // Crear una excepción de ejemplo (día no disponible en el futuro)
            ExcepcionHorario::create([
                'barbero_id' => $barbero->id,
                'fecha' => now()->addDays(15)->format('Y-m-d'),
                'es_disponible' => false,
                'motivo' => 'Día libre',
            ]);

            // Crear una excepción con horario especial
            ExcepcionHorario::create([
                'barbero_id' => $barbero->id,
                'fecha' => now()->addDays(20)->format('Y-m-d'),
                'es_disponible' => true,
                'hora_inicio' => '14:00:00',
                'hora_fin' => '18:00:00',
                'motivo' => 'Horario especial - Solo tarde',
            ]);
        }

        $this->command->info('Horarios de barberos creados exitosamente.');
    }
}
