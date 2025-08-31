<?php

namespace App\Http\Controllers;

use App\Models\DistributorQuotation;
use App\Models\DistributorTechnicalRecord;
use App\Models\ClientCurrentAccount;
use App\Models\DistributorCurrentAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

        // Ventas de presupuestos convertidos (solo si la tabla existe)
        $quotationSales = 0;
        $countQuotations = 0;
        try {
            if (Schema::hasTable('distributor_quotations')) {
                $quotationSales = DistributorQuotation::where('status', 'active')
                    ->whereBetween('created_at', [$startOfDay, $endOfDay])
                    ->sum('final_amount');
                $countQuotations = DistributorQuotation::where('status', 'active')
                    ->whereBetween('created_at', [$startOfDay, $endOfDay])->count();
            }
        } catch (\Exception $e) {
            // Si hay error, asumir que la tabla no existe o no es accesible
            $quotationSales = 0;
            $countQuotations = 0;
        }

        // Ventas de fichas técnicas (solo si la tabla existe)
        $technicalRecordSales = 0;
        $countTechnicalRecords = 0;
        try {
            if (Schema::hasTable('distributor_technical_records')) {
                $technicalRecordSales = DistributorTechnicalRecord::whereBetween('created_at', [$startOfDay, $endOfDay])
                    ->sum('final_amount');
                $countTechnicalRecords = DistributorTechnicalRecord::whereBetween('created_at', [$startOfDay, $endOfDay])->count();
            }
        } catch (\Exception $e) {
            $technicalRecordSales = 0;
            $countTechnicalRecords = 0;
        }

        // Ventas de cuentas corrientes de clientes (solo si la tabla existe)
        $clientAccountSales = 0;
        $countClientAccounts = 0;
        try {
            if (Schema::hasTable('client_current_accounts')) {
                $clientAccountSales = ClientCurrentAccount::whereBetween('created_at', [$startOfDay, $endOfDay])
                    ->sum('amount');
                $countClientAccounts = ClientCurrentAccount::whereBetween('created_at', [$startOfDay, $endOfDay])->count();
            }
        } catch (\Exception $e) {
            $clientAccountSales = 0;
            $countClientAccounts = 0;
        }

        // Ventas de cuentas corrientes de distribuidores (solo si la tabla existe)
        $distributorAccountSales = 0;
        $countDistributorAccounts = 0;
        try {
            if (Schema::hasTable('distributor_current_accounts')) {
                $distributorAccountSales = DistributorCurrentAccount::whereBetween('created_at', [$startOfDay, $endOfDay])
                    ->sum('amount');
                $countDistributorAccounts = DistributorCurrentAccount::whereBetween('created_at', [$startOfDay, $endOfDay])->count();
            }
        } catch (\Exception $e) {
            $distributorAccountSales = 0;
            $countDistributorAccounts = 0;
        }

        $totalSales = $quotationSales + $technicalRecordSales + $clientAccountSales + $distributorAccountSales;

        return [
            'total' => $totalSales,
            'quotations' => $quotationSales,
            'technical_records' => $technicalRecordSales,
            'client_accounts' => $clientAccountSales,
            'distributor_accounts' => $distributorAccountSales,
            'count_quotations' => $countQuotations,
            'count_technical_records' => $countTechnicalRecords,
            'count_client_accounts' => $countClientAccounts,
            'count_distributor_accounts' => $countDistributorAccounts,
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

        // Ventas mensuales de presupuestos (solo si la tabla existe)
        $monthlySales = 0;
        try {
            if (Schema::hasTable('distributor_quotations')) {
                $monthlySales = DistributorQuotation::where('status', 'active')
                    ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->sum('final_amount');
            }
        } catch (\Exception $e) {
            $monthlySales = 0;
        }

        // Ventas mensuales de fichas técnicas (solo si la tabla existe)
        $monthlyTechnicalRecords = 0;
        try {
            if (Schema::hasTable('distributor_technical_records')) {
                $monthlyTechnicalRecords = DistributorTechnicalRecord::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->sum('final_amount');
            }
        } catch (\Exception $e) {
            $monthlyTechnicalRecords = 0;
        }

        // Ventas mensuales de cuentas corrientes de clientes (solo si la tabla existe)
        $monthlyClientAccounts = 0;
        try {
            if (Schema::hasTable('client_current_accounts')) {
                $monthlyClientAccounts = ClientCurrentAccount::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->sum('amount');
            }
        } catch (\Exception $e) {
            $monthlyClientAccounts = 0;
        }

        // Ventas mensuales de cuentas corrientes de distribuidores (solo si la tabla existe)
        $monthlyDistributorAccounts = 0;
        try {
            if (Schema::hasTable('distributor_current_accounts')) {
                $monthlyDistributorAccounts = DistributorCurrentAccount::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->sum('amount');
            }
        } catch (\Exception $e) {
            $monthlyDistributorAccounts = 0;
        }

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

            // Ventas por hora de presupuestos (solo si la tabla existe)
            $quotationSales = 0;
            try {
                if (Schema::hasTable('distributor_quotations')) {
                    $quotationSales = DistributorQuotation::where('status', 'active')
                        ->whereBetween('created_at', [$hourStart, $hourEnd])
                        ->sum('final_amount');
                }
            } catch (\Exception $e) {
                $quotationSales = 0;
            }

            // Ventas por hora de fichas técnicas (solo si la tabla existe)
            $technicalRecordSales = 0;
            try {
                if (Schema::hasTable('distributor_technical_records')) {
                    $technicalRecordSales = DistributorTechnicalRecord::whereBetween('created_at', [$hourStart, $hourEnd])
                        ->sum('final_amount');
                }
            } catch (\Exception $e) {
                $technicalRecordSales = 0;
            }

            // Ventas por hora de cuentas corrientes de clientes (solo si la tabla existe)
            $clientAccountSales = 0;
            try {
                if (Schema::hasTable('client_current_accounts')) {
                    $clientAccountSales = ClientCurrentAccount::whereBetween('created_at', [$hourStart, $hourEnd])
                        ->sum('amount');
                }
            } catch (\Exception $e) {
                $clientAccountSales = 0;
            }

            // Ventas por hora de cuentas corrientes de distribuidores (solo si la tabla existe)
            $distributorAccountSales = 0;
            try {
                if (Schema::hasTable('distributor_current_accounts')) {
                    $distributorAccountSales = DistributorCurrentAccount::whereBetween('created_at', [$hourStart, $hourEnd])
                        ->sum('amount');
                }
            } catch (\Exception $e) {
                $distributorAccountSales = 0;
            }

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