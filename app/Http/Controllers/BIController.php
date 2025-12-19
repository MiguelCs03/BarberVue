<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Venta;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\Barbero;
use App\Models\Cliente;
use App\Models\MovimientoInventario;
use App\Models\Detalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Inertia\Inertia;

class BIController extends Controller
{
    public function index()
    {
        return Inertia::render('BI/Index');
    }

    public function index2()
    {
        return Inertia::render('BI/Index2');
    }

    /**
     * Aplica filtro de fecha dinámico según el request
     */
    private function applyTimeFilter($query, $request, $column = 'created_at')
    {
        $filter = $request->query('filter', 'this_month');

        switch ($filter) {
            case 'today':
                $query->whereDate($column, Carbon::today());
                break;
            case 'this_week':
                $query->whereBetween($column, [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
                break;
            case 'this_month':
                $query->whereMonth($column, Carbon::now()->month)
                    ->whereYear($column, Carbon::now()->year);
                break;
            case 'this_year':
                $query->whereYear($column, Carbon::now()->year);
                break;
        }
        return $query;
    }

    // 1. Barberos más cotizados (Citas por barbero)
    public function getBarberosMasCotizados()
    {
        $data = DB::table('citas')
            ->join('barberos', 'citas.barbero_id', '=', 'barberos.id')
            ->join('users', 'barberos.id', '=', 'users.id')
            ->select('users.name as name', DB::raw('count(citas.id) as total'))
            ->groupBy('barberos.id', 'users.name')
            ->orderByDesc('total')
            ->get();

        return response()->json($data);
    }

    // 2. Servicios más populares
    public function getServiciosMasCotizados()
    {
        $data = DB::table('cita_servicios')
            ->join('servicios', 'cita_servicios.servicio_id', '=', 'servicios.id')
            ->select('servicios.nombre as name', DB::raw('count(cita_servicios.id) as total'))
            ->groupBy('servicios.id', 'servicios.nombre')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return response()->json($data);
    }

    // 3. Ingresos Mensuales (Último año)
    public function getVentasMensuales()
    {
        $data = Venta::select(
            DB::raw("TO_CHAR(created_at, 'MM') as month"),
            DB::raw('SUM(monto_total) as total')
        )
            ->where('created_at', '>=', now()->subYear())
            ->where('estado_pago', 'confirmado')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json($data);
    }

    // 4. Distribución de Ingresos (Productos vs Servicios)
    public function getDistribucionIngresos()
    {
        $productos = DB::table('detalles')
            ->whereNotNull('producto_id')
            ->sum('subtotal');

        $servicios = DB::table('detalles')
            ->whereNotNull('servicio_id')
            ->sum('subtotal');

        return response()->json([
            ['name' => 'Productos', 'total' => (float)$productos],
            ['name' => 'Servicios', 'total' => (float)$servicios]
        ]);
    }

    // 5. Alerta de Stock (Top 10 críticos)
    public function getStockAlert()
    {
        $data = Producto::select('nombre as name', 'stock_actual', 'stock_minimo')
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->orderBy(DB::raw('stock_actual - stock_minimo'))
            ->limit(10)
            ->get()
            ->map(function ($p) {
                return [
                    'name' => $p->name,
                    'stock' => $p->stock_actual,
                    'minimo' => $p->stock_minimo,
                    'deficit' => $p->stock_minimo - $p->stock_actual
                ];
            });

        return response()->json($data);
    }

    // 6. Citas por Estado
    public function getEstadoCitas()
    {
        $data = Cita::select('estado as name', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->get();

        return response()->json($data);
    }

    // 7. Ingresos por Barbero
    public function getIngresosPorBarbero()
    {
        $data = DB::table('ventas')
            ->join('barberos', 'ventas.barbero_id', '=', 'barberos.id')
            ->join('users', 'barberos.id', '=', 'users.id')
            ->select('users.name as name', DB::raw('SUM(ventas.monto_total) as total'))
            ->where('ventas.estado_pago', 'pagado')
            ->groupBy('barberos.id', 'users.name')
            ->orderByDesc('total')
            ->get();

        return response()->json($data);
    }

    // 8. Tendencia de Inventario (Últimos 30 días)
    public function getTendenciaInventario()
    {
        $data = MovimientoInventario::select(
            'tipo_movimiento as name',
            DB::raw('count(*) as total')
        )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('tipo_movimiento')
            ->get();

        return response()->json($data);
    }

    // 9. Crecimiento de Clientes (Mensual)
    public function getCrecimientoClientes()
    {
        $data = Cliente::select(
            DB::raw("TO_CHAR(created_at, 'MM') as month"),
            DB::raw('count(*) as total')
        )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json($data);
    }

    // 10. Popularidad de Métodos de Pago
    // --- MÓDULO BI v2 ---

    // 1. Citas por Barbero (Vertical/Horizontal)
    public function getStatsAppointmentsByBarber(Request $request)
    {
        $query = DB::table('citas')
            ->join('barberos', 'citas.barbero_id', '=', 'barberos.id')
            ->join('users', 'barberos.id', '=', 'users.id')
            ->select('users.name as name', DB::raw('count(citas.id) as total'));

        $this->applyTimeFilter($query, $request, 'citas.fecha');

        $data = $query->groupBy('barberos.id', 'users.name')
            ->orderByDesc('total')
            ->get();

        return response()->json($data);
    }

    // 2. Servicios más populares (Torta)
    public function getStatsPopularServices(Request $request)
    {
        $query = DB::table('cita_servicios')
            ->join('servicios', 'cita_servicios.servicio_id', '=', 'servicios.id')
            ->select('servicios.nombre as name', DB::raw('count(cita_servicios.id) as total'));

        $this->applyTimeFilter($query, $request, 'cita_servicios.created_at');

        $data = $query->groupBy('servicios.id', 'servicios.nombre')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return response()->json($data);
    }

    // 3. Productos más populares (Torta)
    public function getStatsPopularProducts(Request $request)
    {
        $query = DB::table('detalles')
            ->join('productos', 'detalles.producto_id', '=', 'productos.id')
            ->select('productos.nombre as name', DB::raw('sum(detalles.cantidad) as total'))
            ->whereNotNull('detalles.producto_id');

        $this->applyTimeFilter($query, $request, 'detalles.created_at');

        $data = $query->groupBy('productos.id', 'productos.nombre')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return response()->json($data);
    }

    // 4. Tipos de Pago (Barras)
    public function getStatsPaymentTypes(Request $request)
    {
        $type = $request->query('type', 'citas'); // 'citas' o 'ventas'

        if ($type === 'citas') {
            $query = DB::table('citas')
                ->join('tipo_pagos', 'citas.tipo_pago_id', '=', 'tipo_pagos.id')
                ->select('tipo_pagos.nombre as name', DB::raw('count(citas.id) as total'));
            $this->applyTimeFilter($query, $request, 'citas.created_at');
        } else {
            $query = DB::table('detalle_pagos')
                ->join('tipo_pagos', 'detalle_pagos.tipo_pago_id', '=', 'tipo_pagos.id')
                ->select('tipo_pagos.nombre as name', DB::raw('count(detalle_pagos.id) as total'));
            $this->applyTimeFilter($query, $request, 'detalle_pagos.created_at');
        }

        $data = $query->groupBy('tipo_pagos.id', 'tipo_pagos.nombre')->get();

        return response()->json($data);
    }

    // 5. Movimientos de Inventario (Torta)
    public function getStatsInventoryMovements(Request $request)
    {
        $query = DB::table('movimiento_inventarios')
            ->select('tipo_movimiento as name', DB::raw('count(*) as total'));

        $this->applyTimeFilter($query, $request, 'created_at');

        $data = $query->groupBy('tipo_movimiento')->get();

        return response()->json($data);
    }

    // 6. Crecimiento de Clientes y Barberos (Lineal)
    public function getStatsUserGrowth(Request $request)
    {
        $filter = $request->query('filter', 'this_month');

        // Determinamos el formato de agrupamiento según el filtro
        $groupByFormat = match ($filter) {
            'this_year' => 'YYYY-MM',
            'this_month' => 'YYYY-MM-DD',
            'this_week' => 'YYYY-MM-DD',
            default => 'YYYY-MM-DD HH12'
        };

        // Clientes por periodo
        $clientsQuery = DB::table('clientes')
            ->select(DB::raw("TO_CHAR(created_at, '$groupByFormat') as period"), DB::raw('count(*) as total'));
        $this->applyTimeFilter($clientsQuery, $request, 'created_at');
        $clientsData = $clientsQuery->groupBy('period')->orderBy('period')->get();

        // Barberos por periodo
        $barbersQuery = DB::table('barberos')
            ->select(DB::raw("TO_CHAR(created_at, '$groupByFormat') as period"), DB::raw('count(*) as total'));
        $this->applyTimeFilter($barbersQuery, $request, 'created_at');
        $barbersData = $barbersQuery->groupBy('period')->orderBy('period')->get();

        // Unificar categorías (X-axis)
        $categories = collect($clientsData->pluck('period'))
            ->merge($barbersData->pluck('period'))
            ->unique()
            ->sort()
            ->values();

        // Mapear series
        $series = [
            [
                'name' => 'Clientes',
                'data' => $categories->map(fn($cat) => $clientsData->firstWhere('period', $cat)?->total ?? 0)
            ],
            [
                'name' => 'Barberos',
                'data' => $categories->map(fn($cat) => $barbersData->firstWhere('period', $cat)?->total ?? 0)
            ]
        ];

        return response()->json([
            [
                'categories' => $categories,
                'series' => $series
            ]
        ]);
    }

    // 7. Tendencia de Ingresos (Lineal)
    public function getStatsIncomeTrend(Request $request)
    {
        $filter = $request->query('filter', 'this_month');
        $groupByFormat = match ($filter) {
            'this_year' => 'YYYY-MM',
            'this_month' => 'YYYY-MM-DD',
            'this_week' => 'YYYY-MM-DD',
            default => 'YYYY-MM-DD'
        };

        $query = DB::table('ventas')
            ->select(
                'estado_pago as status',
                DB::raw("TO_CHAR(created_at, '$groupByFormat') as period"),
                DB::raw('SUM(monto_total) as total')
            );

        $this->applyTimeFilter($query, $request, 'created_at');

        $data = $query->groupBy('status', 'period')
            ->orderBy('period')
            ->get();

        return $this->formatMultiSeriesResponse($data, 'period', 'status', 'total');
    }

    // --- MÓDULO BI v3 ---

    // 1. Horas Pico de Citas (Barras)
    public function getStatsCitasPorHora(Request $request)
    {
        $query = DB::table('citas')
            ->select(
                DB::raw("TO_CHAR(fecha, 'HH24') || ':00' as name"),
                DB::raw('count(*) as total')
            );

        $this->applyTimeFilter($query, $request, 'fecha');

        $data = $query->groupBy('name')
            ->orderBy('name')
            ->get();

        return response()->json($data);
    }

    // 2. Eficiencia de Citas (Completadas vs Canceladas vs Otros)
    public function getStatsEficienciaCitas(Request $request)
    {
        $query = DB::table('citas')
            ->select('estado as name', DB::raw('count(*) as total'));

        $this->applyTimeFilter($query, $request, 'fecha');

        $data = $query->groupBy('estado')->get();

        return response()->json($data);
    }

    // 3. Ventas por Día de la Semana (Radiales o Barras)
    public function getStatsVentasPorDiaSemana(Request $request)
    {
        $query = DB::table('ventas')
            ->where('estado_pago', 'confirmado')
            ->select(
                DB::raw("TO_CHAR(created_at, 'Day') as name"),
                DB::raw("TO_CHAR(created_at, 'D') as day_num"), // Para ordenar
                DB::raw('SUM(monto_total) as total')
            );

        $this->applyTimeFilter($query, $request, 'created_at');

        $data = $query->groupBy('name', 'day_num')
            ->orderBy('day_num')
            ->get();

        return response()->json($data);
    }

    // 4. Ticket Promedio Mensual (Lineal)
    public function getStatsTicketPromedioMensual(Request $request)
    {
        $query = DB::table('ventas')
            ->where('estado_pago', 'confirmado')
            ->select(
                DB::raw("TO_CHAR(created_at, 'YYYY-MM') as name"),
                DB::raw('AVG(monto_total) as total')
            );

        $this->applyTimeFilter($query, $request, 'created_at');

        $data = $query->groupBy('name')
            ->orderBy('name')
            ->get();

        return response()->json($data);
    }

    // 5. Distribución de Usuarios por Rol (Torta)
    public function getStatsRolesDistribucion()
    {
        $data = DB::table('users')
            ->select('rol as name', DB::raw('count(*) as total'))
            ->groupBy('rol')
            ->get();

        return response()->json($data);
    }

    // 6. Servicios por Duración Total (Eficiencia Operativa)
    public function getStatsServiciosPorDuracion(Request $request)
    {
        $query = DB::table('cita_servicios')
            ->join('servicios', 'cita_servicios.servicio_id', '=', 'servicios.id')
            ->select(
                'servicios.nombre as name',
                DB::raw('SUM(servicios.duracion_estimada) as total')
            );

        $this->applyTimeFilter($query, $request, 'cita_servicios.created_at');

        $data = $query->groupBy('servicios.id', 'servicios.nombre')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return response()->json($data);
    }
    public function getStatsKPIs(Request $request)
    {
        // Ingresos Totales (Mes actual vs Mes anterior)
        $currentMonthIncome = DB::table('ventas')
            ->where('estado_pago', 'confirmado')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('monto_total');

        $lastMonthIncome = DB::table('ventas')
            ->where('estado_pago', 'confirmado')
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->sum('monto_total');

        $incomeTrend = $lastMonthIncome > 0 ? (($currentMonthIncome - $lastMonthIncome) / $lastMonthIncome) * 100 : 100;

        // Citas Hoy
        $citasHoy = DB::table('citas')->whereDate('fecha', Carbon::today())->count();
        $citasAyer = DB::table('citas')->whereDate('fecha', Carbon::yesterday())->count();
        $citasTrend = $citasAyer > 0 ? (($citasHoy - $citasAyer) / $citasAyer) * 100 : 100;

        // Clientes Nuevos
        $nuevosClientes = DB::table('clientes')->whereMonth('created_at', Carbon::now()->month)->count();
        $clientesAnteriores = DB::table('clientes')->whereMonth('created_at', Carbon::now()->subMonth()->month)->count();
        $clientesTrend = $clientesAnteriores > 0 ? (($nuevosClientes - $clientesAnteriores) / $clientesAnteriores) * 100 : 100;

        // Stock Crítico
        $stockCritico = DB::table('productos')->whereColumn('stock_actual', '<=', 'stock_minimo')->count();

        return response()->json([
            [
                'id' => 'ingresos',
                'titulo' => 'Ingresos Mensuales',
                'valor' => 'Bs ' . number_format($currentMonthIncome, 2),
                'tendencia' => $incomeTrend >= 0 ? 'up' : 'down',
                'porcentaje' => round(abs($incomeTrend), 1),
                'icono' => 'ingresos',
                'gradiente' => 'from-emerald-500 to-green-600',
            ],
            [
                'id' => 'citas',
                'titulo' => 'Citas para Hoy',
                'valor' => (string)$citasHoy,
                'tendencia' => $citasTrend >= 0 ? 'up' : 'down',
                'porcentaje' => round(abs($citasTrend), 1),
                'icono' => 'reservas',
                'gradiente' => 'from-blue-500 to-indigo-600',
            ],
            [
                'id' => 'clientes',
                'titulo' => 'Nuevos Clientes',
                'valor' => (string)$nuevosClientes,
                'tendencia' => $clientesTrend >= 0 ? 'up' : 'down',
                'porcentaje' => round(abs($clientesTrend), 1),
                'icono' => 'ocupacion',
                'gradiente' => 'from-purple-500 to-pink-600',
            ],
            [
                'id' => 'stock',
                'titulo' => 'Productos Críticos',
                'valor' => (string)$stockCritico,
                'tendencia' => 'down',
                'porcentaje' => $stockCritico,
                'icono' => 'satisfaccion',
                'gradiente' => 'from-orange-500 to-red-600',
            ]
        ]);
    }

    public function getStatsStockAlert()
    {
        $data = Producto::select('nombre as name', 'stock_actual', 'stock_minimo')
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->orderBy(DB::raw('stock_actual - stock_minimo'))
            ->limit(10)
            ->get()
            ->map(function ($p) {
                return [
                    'name' => $p->name,
                    'total' => $p->stock_minimo - $p->stock_actual,
                    'stock' => $p->stock_actual,
                    'minimo' => $p->stock_minimo
                ];
            });

        return response()->json($data);
    }

    public function getStatsIngresosPorBarbero(Request $request)
    {
        $query = DB::table('ventas')
            ->join('barberos', 'ventas.barbero_id', '=', 'barberos.id')
            ->join('users', 'barberos.id', '=', 'users.id')
            ->select('users.name as name', DB::raw('SUM(ventas.monto_total) as total'))
            ->where('ventas.estado_pago', 'confirmado');

        $this->applyTimeFilter($query, $request, 'ventas.created_at');

        $data = $query->groupBy('barberos.id', 'users.name')
            ->orderByDesc('total')
            ->get();

        return response()->json($data);
    }
    /**
     * Tendencia de Movimientos de Inventario (Multi-serie)
     */
    public function getStatsInventoryTrend(Request $request)
    {
        $filter = $request->query('filter', 'this_month');
        $groupByFormat = match ($filter) {
            'this_year' => 'YYYY-MM',
            'this_month' => 'YYYY-MM-DD',
            'this_week' => 'YYYY-MM-DD',
            default => 'YYYY-MM-DD'
        };

        $query = DB::table('movimiento_inventarios')
            ->select(
                'tipo_movimiento as type',
                DB::raw("TO_CHAR(created_at, '$groupByFormat') as period"),
                DB::raw('count(*) as total')
            );

        $this->applyTimeFilter($query, $request, 'created_at');

        $data = $query->groupBy('type', 'period')
            ->orderBy('period')
            ->get();

        return $this->formatMultiSeriesResponse($data, 'period', 'type', 'total');
    }

    /**
     * Tendencia de Estados de Citas (Multi-serie)
     */
    public function getStatsAppointmentsStatusTrend(Request $request)
    {
        $filter = $request->query('filter', 'this_month');
        $groupByFormat = match ($filter) {
            'this_year' => 'YYYY-MM',
            'this_month' => 'YYYY-MM-DD',
            'this_week' => 'YYYY-MM-DD',
            default => 'YYYY-MM-DD'
        };

        $query = DB::table('citas')
            ->select(
                'estado',
                DB::raw("TO_CHAR(fecha, '$groupByFormat') as period"),
                DB::raw('count(*) as total')
            );

        $this->applyTimeFilter($query, $request, 'fecha');

        $data = $query->groupBy('estado', 'period')
            ->orderBy('period')
            ->get();

        return $this->formatMultiSeriesResponse($data, 'period', 'estado', 'total');
    }

    /**
     * Tendencia de Estados de Ventas (Multi-serie)
     */
    public function getStatsSalesStatusTrend(Request $request)
    {
        $filter = $request->query('filter', 'this_month');
        $groupByFormat = match ($filter) {
            'this_year' => 'YYYY-MM',
            'this_month' => 'YYYY-MM-DD',
            'this_week' => 'YYYY-MM-DD',
            default => 'YYYY-MM-DD'
        };

        $query = DB::table('ventas')
            ->select(
                'estado_pago as status',
                DB::raw("TO_CHAR(created_at, '$groupByFormat') as period"),
                DB::raw('count(*) as total')
            );

        $this->applyTimeFilter($query, $request, 'created_at');

        $data = $query->groupBy('status', 'period')
            ->orderBy('period')
            ->get();

        return $this->formatMultiSeriesResponse($data, 'period', 'status', 'total');
    }

    /**
     * Helper para formatear respuestas multi-serie de ApexCharts
     */
    private function formatMultiSeriesResponse($data, $categoryKey, $seriesKey, $valueKey)
    {
        $categories = $data->pluck($categoryKey)->unique()->sort()->values();
        $seriesNames = $data->pluck($seriesKey)->unique()->values();

        $series = $seriesNames->map(function ($name) use ($data, $categories, $categoryKey, $seriesKey, $valueKey) {
            return [
                'name' => ucfirst($name),
                'data' => $categories->map(function ($cat) use ($data, $name, $categoryKey, $seriesKey, $valueKey) {
                    return $data->where($categoryKey, $cat)->where($seriesKey, $name)->first()?->{$valueKey} ?? 0;
                })
            ];
        });

        return response()->json([
            [
                'categories' => $categories,
                'series' => $series
            ]
        ]);
    }
}
