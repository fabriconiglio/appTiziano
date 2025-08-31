<?php

namespace App\Http\Controllers;

use App\Models\ClientCurrentAccount;
use App\Models\TechnicalRecord;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class HairdressingDailySalesController extends Controller
{
    /**
     * Mostrar el dashboard de ventas diarias de peluquería
     */
    public function index(Request $request)
    {
        $today = Carbon::today();
        
        // Obtener fecha seleccionada o usar hoy por defecto
        $selectedDate = $request->has('selected_date') && $request->selected_date 
            ? Carbon::parse($request->selected_date) 
            : $today;
        
        // Asegurar que no se pueda seleccionar una fecha futura
        if ($selectedDate->gt($today)) {
            $selectedDate = $today;
        }
        
        $yesterday = $selectedDate->copy()->subDay();
        
        // Obtener ventas de la fecha seleccionada
        $todaySales = $this->getDailySales($selectedDate);
        
        // Obtener ventas del día anterior para comparación
        $yesterdaySales = $this->getDailySales($yesterday);
        
        // Obtener estadísticas del mes de la fecha seleccionada
        $monthlyStats = $this->getMonthlyStats($selectedDate);
        
        // Obtener ventas por hora de la fecha seleccionada
        $hourlySales = $this->getHourlySales($selectedDate);
        
        // Obtener servicios más populares del día
        $popularServices = $this->getPopularServices($selectedDate);
        
        // Obtener productos más vendidos del día
        $popularProducts = $this->getPopularProducts($selectedDate);
        
        return view('hairdressing_daily_sales.index', compact(
            'todaySales', 
            'yesterdaySales', 
            'monthlyStats', 
            'hourlySales',
            'popularServices',
            'popularProducts',
            'today',
            'selectedDate'
        ));
    }

    /**
     * Obtener ventas de un día específico para peluquería
     */
    private function getDailySales($date)
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        // Ventas de cuentas corrientes de clientes (deudas)
        $clientAccountSales = ClientCurrentAccount::where('type', 'debt')
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->sum('amount');

        // Ventas de fichas técnicas (costo real del servicio)
        $technicalRecordSales = TechnicalRecord::whereBetween('service_date', [$startOfDay, $endOfDay])
            ->sum('service_cost');

        // Ventas de productos vendidos (estimación basada en salidas de stock)
        $productSales = StockMovement::where('type', 'salida')
            ->whereBetween('stock_movements.created_at', [$startOfDay, $endOfDay])
            ->join('products', 'stock_movements.product_id', '=', 'products.id')
            ->sum(DB::raw('stock_movements.quantity * COALESCE(products.price, 0)'));

        // Ventas de servicios adicionales (50% del costo total)
        $additionalServices = TechnicalRecord::whereBetween('service_date', [$startOfDay, $endOfDay])
            ->sum('service_cost') * 0.5; // 50% del costo total como servicios adicionales

        $totalSales = $clientAccountSales + $technicalRecordSales + $productSales + $additionalServices;

        return [
            'total' => $totalSales,
            'client_accounts' => $clientAccountSales,
            'technical_records' => $technicalRecordSales,
            'product_sales' => $productSales,
            'additional_services' => $additionalServices,
            'count_client_accounts' => ClientCurrentAccount::where('type', 'debt')
                ->whereBetween('created_at', [$startOfDay, $endOfDay])->count(),
            'count_technical_records' => TechnicalRecord::whereBetween('service_date', [$startOfDay, $endOfDay])->count(),
            'count_product_sales' => StockMovement::where('type', 'salida')
                ->whereBetween('stock_movements.created_at', [$startOfDay, $endOfDay])->count(),
        ];
    }

    /**
     * Obtener estadísticas del mes de una fecha específica
     */
    private function getMonthlyStats($date = null)
    {
        if (!$date) {
            $date = Carbon::now();
        }
        
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $monthlyClientAccounts = ClientCurrentAccount::where('type', 'debt')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $monthlyTechnicalRecords = TechnicalRecord::whereBetween('service_date', [$startOfMonth, $endOfMonth])
            ->sum('service_cost');

        $monthlyProductSales = StockMovement::where('type', 'salida')
            ->whereBetween('stock_movements.created_at', [$startOfMonth, $endOfMonth])
            ->join('products', 'stock_movements.product_id', '=', 'products.id')
            ->sum(DB::raw('stock_movements.quantity * COALESCE(products.price, 0)'));

        $monthlyAdditionalServices = TechnicalRecord::whereBetween('service_date', [$startOfMonth, $endOfMonth])
            ->sum('service_cost') * 0.5; // 50% del costo total como servicios adicionales

        return [
            'total' => $monthlyClientAccounts + $monthlyTechnicalRecords + $monthlyProductSales + $monthlyAdditionalServices,
            'client_accounts' => $monthlyClientAccounts,
            'technical_records' => $monthlyTechnicalRecords,
            'product_sales' => $monthlyProductSales,
            'additional_services' => $monthlyAdditionalServices,
        ];
    }

    /**
     * Obtener ventas por hora del día
     */
    private function getHourlySales($date)
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        $hourlyData = [];

        for ($hour = 0; $hour < 24; $hour++) {
            $hourStart = $startOfDay->copy()->addHours($hour);
            $hourEnd = $hourStart->copy()->addHour();

            $clientAccountSales = ClientCurrentAccount::where('type', 'debt')
                ->whereBetween('created_at', [$hourStart, $hourEnd])
                ->sum('amount');

            $technicalRecordSales = TechnicalRecord::whereBetween('service_date', [$hourStart, $hourEnd])
                ->sum('service_cost');

            $productSales = StockMovement::where('type', 'salida')
                ->whereBetween('stock_movements.created_at', [$hourStart, $hourEnd])
                ->join('products', 'stock_movements.product_id', '=', 'products.id')
                ->sum(DB::raw('stock_movements.quantity * COALESCE(products.price, 0)'));

            $additionalServices = TechnicalRecord::whereBetween('service_date', [$hourStart, $hourEnd])
                ->sum('service_cost') * 0.5; // 50% del costo total como servicios adicionales

            $hourlyData[$hour] = [
                'hour' => $hour,
                'label' => sprintf('%02d:00', $hour),
                'total' => $clientAccountSales + $technicalRecordSales + $productSales + $additionalServices,
                'client_accounts' => $clientAccountSales,
                'technical_records' => $technicalRecordSales,
                'product_sales' => $productSales,
                'additional_services' => $additionalServices,
            ];
        }

        return $hourlyData;
    }

    /**
     * Obtener servicios más populares del día
     */
    private function getPopularServices($date)
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        return TechnicalRecord::whereBetween('service_date', [$startOfDay, $endOfDay])
            ->select('hair_treatments', 'service_type', DB::raw('count(*) as total'), DB::raw('sum(service_cost) as total_cost'))
            ->whereNotNull('hair_treatments')
            ->groupBy('hair_treatments', 'service_type')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Obtener productos más vendidos del día
     */
    private function getPopularProducts($date)
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        return StockMovement::where('type', 'salida')
            ->whereBetween('stock_movements.created_at', [$startOfDay, $endOfDay])
            ->join('products', 'stock_movements.product_id', '=', 'products.id')
            ->select('products.name', 'products.description', DB::raw('sum(stock_movements.quantity) as total_quantity'), DB::raw('sum(stock_movements.quantity * COALESCE(products.price, 0)) as total_amount'))
            ->groupBy('products.id', 'products.name', 'products.description')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Obtener datos para gráficos (API)
     */
    public function getChartData()
    {
        $today = Carbon::today();
        $hourlySales = $this->getHourlySales($today);
        
        return response()->json([
            'labels' => array_column($hourlySales, 'label'),
            'datasets' => [
                [
                    'label' => 'Ventas por Hora',
                    'data' => array_column($hourlySales, 'total'),
                    'borderColor' => 'rgb(255, 99, 132)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'tension' => 0.1
                ]
            ]
        ]);
    }

    /**
     * Exportar reporte diario a PDF
     */
    public function exportPdf()
    {
        $today = Carbon::today();
        $todaySales = $this->getDailySales($today);
        $hourlySales = $this->getHourlySales($today);
        $popularServices = $this->getPopularServices($today);
        $popularProducts = $this->getPopularProducts($today);
        
        $pdf = Pdf::loadView('hairdressing_daily_sales.pdf', compact(
            'todaySales', 
            'hourlySales', 
            'popularServices', 
            'popularProducts', 
            'today'
        ));
        
        return $pdf->download('ventas-peluqueria-dia-' . $today->format('Y-m-d') . '.pdf');
    }
} 