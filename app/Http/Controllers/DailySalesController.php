<?php

namespace App\Http\Controllers;

use App\Models\DistributorQuotation;
use App\Models\DistributorTechnicalRecord;
use App\Models\ClientCurrentAccount;
use App\Models\DistributorCurrentAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class DailySalesController extends Controller
{
    /**
     * Mostrar el dashboard de ventas diarias
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
        
        return view('daily_sales.index', compact(
            'todaySales', 
            'yesterdaySales', 
            'monthlyStats', 
            'hourlySales',
            'today',
            'selectedDate'
        ));
    }

    /**
     * Obtener ventas de un día específico
     */
    private function getDailySales($date)
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        // Ventas de presupuestos convertidos
        $quotationSales = DistributorQuotation::where('status', 'active')
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->sum('final_amount');

        // Ventas de fichas técnicas
        $technicalRecordSales = DistributorTechnicalRecord::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->sum('final_amount');

        // Ventas de cuentas corrientes de clientes
        $clientAccountSales = ClientCurrentAccount::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->sum('amount');

        // Ventas de cuentas corrientes de distribuidores
        $distributorAccountSales = DistributorCurrentAccount::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->sum('amount');

        $totalSales = $quotationSales + $technicalRecordSales + $clientAccountSales + $distributorAccountSales;

        return [
            'total' => $totalSales,
            'quotations' => $quotationSales,
            'technical_records' => $technicalRecordSales,
            'client_accounts' => $clientAccountSales,
            'distributor_accounts' => $distributorAccountSales,
            'count_quotations' => DistributorQuotation::where('status', 'active')
                ->whereBetween('created_at', [$startOfDay, $endOfDay])->count(),
            'count_technical_records' => DistributorTechnicalRecord::whereBetween('created_at', [$startOfDay, $endOfDay])->count(),
            'count_client_accounts' => ClientCurrentAccount::whereBetween('created_at', [$startOfDay, $endOfDay])->count(),
            'count_distributor_accounts' => DistributorCurrentAccount::whereBetween('created_at', [$startOfDay, $endOfDay])->count(),
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

        $monthlySales = DistributorQuotation::where('status', 'active')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('final_amount');

        $monthlyTechnicalRecords = DistributorTechnicalRecord::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('final_amount');

        $monthlyClientAccounts = ClientCurrentAccount::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $monthlyDistributorAccounts = DistributorCurrentAccount::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        return [
            'total' => $monthlySales + $monthlyTechnicalRecords + $monthlyClientAccounts + $monthlyDistributorAccounts,
            'quotations' => $monthlySales,
            'technical_records' => $monthlyTechnicalRecords,
            'client_accounts' => $monthlyClientAccounts,
            'distributor_accounts' => $monthlyDistributorAccounts,
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

            $quotationSales = DistributorQuotation::where('status', 'active')
                ->whereBetween('created_at', [$hourStart, $hourEnd])
                ->sum('final_amount');

            $technicalRecordSales = DistributorTechnicalRecord::whereBetween('created_at', [$hourStart, $hourEnd])
                ->sum('final_amount');

            $clientAccountSales = ClientCurrentAccount::whereBetween('created_at', [$hourStart, $hourEnd])
                ->sum('amount');

            $distributorAccountSales = DistributorCurrentAccount::whereBetween('created_at', [$hourStart, $hourEnd])
                ->sum('amount');

            $hourlyData[$hour] = [
                'hour' => $hour,
                'label' => sprintf('%02d:00', $hour),
                'total' => $quotationSales + $technicalRecordSales + $clientAccountSales + $distributorAccountSales,
                'quotations' => $quotationSales,
                'technical_records' => $technicalRecordSales,
                'client_accounts' => $clientAccountSales,
                'distributor_accounts' => $distributorAccountSales,
            ];
        }

        return $hourlyData;
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
                    'borderColor' => 'rgb(75, 192, 192)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
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
        
        $pdf = Pdf::loadView('daily_sales.pdf', compact('todaySales', 'hourlySales', 'today'));
        
        return $pdf->download('ventas-dia-' . $today->format('Y-m-d') . '.pdf');
    }
} 