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

        $diasLiteral = [
            1 => 'lunes',
            2 => 'martes',
            3 => 'miercoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sabado',
            7 => 'domingo'
        ];

        foreach ($barberos as $barbero) {
            // Lunes a Viernes
            for ($i = 1; $i <= 5; $i++) {
                HorarioBarbero::create([
                    'barbero_id' => $barbero->id,
                    'dia_semana' => $diasLiteral[$i], // Inserta 'lunes', 'martes', etc.
                    'hora_inicio' => '09:00:00',
                    'hora_fin' => '18:00:00',
                ]);
            }

            // Sábado
            HorarioBarbero::create([
                'barbero_id' => $barbero->id,
                'dia_semana' => 'sabado',
                'hora_inicio' => '10:00:00',
                'hora_fin' => '18:00:00',
            ]);

            // Excepciones (esto se mantiene igual ya que usa fechas Y-m-d)
            ExcepcionHorario::create([
                'barbero_id' => $barbero->id,
                'fecha' => now()->addDays(15)->format('Y-m-d'),
                'es_disponible' => false,
                'motivo' => 'Día libre',
            ]);
        }

        $this->command->info('Horarios creados con nombres de días (Enum) exitosamente.');
    }
}
