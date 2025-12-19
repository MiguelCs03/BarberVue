<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\MovimientoInventario;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $inicioMes = $now->copy()->startOfMonth();
        $finMes = $now->copy()->endOfMonth();

        $ventasMes = $this->sumarIngresosEntre($inicioMes, $finMes);
        $ventasTotales = $this->sumarIngresosTotales();
        $citasPagadas = $this->contarCitasPagadas();
        $ticketPromedio = $citasPagadas > 0 ? round($ventasTotales / $citasPagadas, 2) : 0;

        $ingresosMensuales = $this->buildIngresosMensualesDataset($now);
        $estadoCitasDataset = $this->buildEstadoCitasDataset();
        $ventasPorProducto = $this->obtenerVentasPorProducto();
        $citasPorBarbero = $this->obtenerCitasPorBarbero();
        $detalleVentas = $this->obtenerDetalleVentasRecientes();
        $movimientosInventario = $this->obtenerMovimientosInventario();
        $inventarioResumen = $this->resumenInventario();

        return Inertia::render('Reports/Index', [
            'stats' => [
                'ingresosMes' => (float) $ventasMes,
                'citasHoy' => Cita::whereDate('fecha', $now->toDateString())->count(),
                'citasPendientes' => Cita::where('estado', 'pendiente')->count(),
            ],
            'salesSummary' => [
                'ventasTotales' => (float) $ventasTotales,
                'ventasMesActual' => (float) $ventasMes,
                'ticketPromedio' => (float) $ticketPromedio,
                'citasCobradas' => $citasPagadas,
            ],
            'charts' => [
                'ingresos' => $ingresosMensuales,
                'estados' => $estadoCitasDataset,
            ],
            'productSales' => $ventasPorProducto,
            'barberStats' => $citasPorBarbero,
            'recentSales' => $detalleVentas,
            'inventoryMovements' => $movimientosInventario,
            'inventorySummary' => $inventarioResumen,
        ]);
    }

    private function sumarIngresosEntre(Carbon $inicio, Carbon $fin): float
    {
        return (float) Cita::whereIn('estado', ['confirmada', 'completada'])
            ->whereBetween('fecha', [$inicio, $fin])
            ->sum('monto_total');
    }

    private function sumarIngresosTotales(): float
    {
        return (float) Cita::whereIn('estado', ['confirmada', 'completada'])
            ->sum('monto_total');
    }

    private function contarCitasPagadas(): int
    {
        return Cita::whereIn('estado', ['confirmada', 'completada'])->count();
    }

    private function monthFormatExpression(string $column = 'fecha'): string
    {
        $driver = DB::connection()->getDriverName();
        return $driver === 'sqlite'
            ? "strftime('%Y-%m', {$column})"
            : "DATE_FORMAT({$column}, '%Y-%m')";
    }

    private function buildIngresosMensualesDataset(Carbon $reference): array
    {
        $inicio = $reference->copy()->subMonths(5)->startOfMonth();
        $fin = $reference->copy()->endOfMonth();
        $monthExpression = $this->monthFormatExpression('fecha');

        $ingresos = Cita::select(
                DB::raw('sum(monto_total) as total'),
                DB::raw("{$monthExpression} as mes")
            )
            ->whereIn('estado', ['confirmada', 'completada'])
            ->whereBetween('fecha', [$inicio, $fin])
            ->groupBy('mes')
            ->orderBy('mes')
            ->get()
            ->keyBy('mes');

        $labels = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $mes = $reference->copy()->subMonths($i)->startOfMonth();
            $key = $mes->format('Y-m');
            $labels[] = $mes->translatedFormat('M Y');
            $data[] = (float) ($ingresos[$key]->total ?? 0);
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    private function buildEstadoCitasDataset(): array
    {
        $ordenEstados = ['pendiente', 'confirmada', 'cancelada', 'completada'];

        $estados = Cita::select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->get()
            ->keyBy('estado');

        $labels = [];
        $data = [];

        foreach ($ordenEstados as $estado) {
            $labels[] = ucfirst($estado);
            $data[] = (int) ($estados[$estado]->total ?? 0);
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    private function obtenerVentasPorProducto()
    {
        return MovimientoInventario::select(
                'producto_id',
                DB::raw('SUM(cantidad) as total_vendida')
            )
            ->with('producto:id,nombre,precio_venta')
            ->where('tipo_movimiento', 'salida')
            ->groupBy('producto_id')
            ->orderByDesc('total_vendida')
            ->limit(6)
            ->get()
            ->map(function ($venta) {
                $producto = $venta->producto;
                $precio = $producto?->precio_venta ?? 0;

                return [
                    'producto' => $producto?->nombre ?? 'Producto no disponible',
                    'cantidad' => (int) $venta->total_vendida,
                    'ingresos' => round($precio * (int) $venta->total_vendida, 2),
                ];
            });
    }

    private function obtenerCitasPorBarbero()
    {
        return Cita::select(
                'barbero_id',
                DB::raw('COUNT(*) as total_citas'),
                DB::raw("SUM(CASE WHEN estado = 'completada' THEN 1 ELSE 0 END) as completadas"),
                DB::raw('SUM(monto_total) as ingresos')
            )
            ->with('barbero.usuario:id,name')
            ->whereNotNull('barbero_id')
            ->groupBy('barbero_id')
            ->orderByDesc('ingresos')
            ->limit(6)
            ->get()
            ->map(function ($registro) {
                return [
                    'barbero' => $registro->barbero?->usuario?->name ?? 'Sin asignar',
                    'total_citas' => (int) $registro->total_citas,
                    'completadas' => (int) $registro->completadas,
                    'ingresos' => (float) $registro->ingresos,
                ];
            });
    }

    private function obtenerDetalleVentasRecientes()
    {
        return Cita::with([
                'cliente.usuario:id,name',
                'barbero.usuario:id,name',
                'citaServicios.servicio:id,nombre',
            ])
            ->whereIn('estado', ['confirmada', 'completada'])
            ->orderByDesc('fecha')
            ->limit(6)
            ->get()
            ->map(function ($cita) {
                $servicios = $cita->citaServicios->map(function ($detalle) {
                    return $detalle->servicio->nombre ?? null;
                })->filter()->values();

                return [
                    'id' => $cita->id,
                    'cliente' => $cita->cliente?->usuario?->name ?? 'Cliente no disponible',
                    'barbero' => $cita->barbero?->usuario?->name ?? 'Por asignar',
                    'fecha' => Carbon::parse($cita->fecha)->format('d/m/Y H:i'),
                    'estado' => ucfirst($cita->estado),
                    'monto' => (float) $cita->monto_total,
                    'servicios' => $servicios,
                ];
            });
    }

    private function obtenerMovimientosInventario()
    {
        return MovimientoInventario::with('producto:id,nombre')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->map(function ($movimiento) {
                return [
                    'id' => $movimiento->id,
                    'producto' => $movimiento->producto?->nombre ?? 'Producto eliminado',
                    'tipo' => ucfirst($movimiento->tipo_movimiento),
                    'cantidad' => (int) $movimiento->cantidad,
                    'motivo' => $movimiento->motivo,
                    'fecha' => $movimiento->created_at?->format('d/m/Y H:i'),
                ];
            });
    }

    private function resumenInventario(): array
    {
        $resumen = MovimientoInventario::select(
                DB::raw("SUM(CASE WHEN tipo_movimiento = 'entrada' THEN cantidad ELSE 0 END) as entradas"),
                DB::raw("SUM(CASE WHEN tipo_movimiento = 'salida' THEN cantidad ELSE 0 END) as salidas"),
                DB::raw("SUM(CASE WHEN tipo_movimiento = 'ajuste' THEN 1 ELSE 0 END) as ajustes")
            )
            ->first();

        return [
            'entradas' => (int) ($resumen?->entradas ?? 0),
            'salidas' => (int) ($resumen?->salidas ?? 0),
            'ajustes' => (int) ($resumen?->ajustes ?? 0),
        ];
    }

    public function exportVentas(\Illuminate\Http\Request $request)
    {
        $filename = "ventas_" . date('Ymd_His') . ".csv";
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['ID', 'Cliente', 'Barbero', 'Monto Total', 'Estado Pago', 'Fecha'];

        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, $columns);

            $ventas = \App\Models\Venta::with(['cliente.usuario', 'barbero.usuario'])->orderBy('created_at', 'desc')->get();

            foreach ($ventas as $venta) {
                fputcsv($file, [
                    $venta->id,
                    $venta->cliente->usuario->name ?? 'N/A',
                    $venta->barbero->usuario->name ?? 'N/A',
                    $venta->monto_total,
                    $venta->estado_pago,
                    $venta->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportCitas(\Illuminate\Http\Request $request)
    {
        $filename = "citas_" . date('Ymd_His') . ".csv";
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['ID', 'Cliente', 'Barbero', 'Fecha', 'Hora', 'Estado', 'Monto'];

        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            $citas = Cita::with(['cliente.usuario', 'barbero.usuario'])->orderBy('fecha', 'desc')->get();

            foreach ($citas as $cita) {
                fputcsv($file, [
                    $cita->id,
                    $cita->cliente->usuario->name ?? 'N/A',
                    $cita->barbero->usuario->name ?? 'N/A',
                    $cita->fecha,
                    $cita->hora,
                    $cita->estado,
                    $cita->monto_total,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

