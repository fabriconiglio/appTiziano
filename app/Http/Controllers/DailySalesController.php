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
        
        // Validar fechas de entrada
        $request->validate([
            'start_date' => 'nullable|date|before_or_equal:today',
            'end_date' => 'nullable|date|before_or_equal:today|after_or_equal:start_date'
        ], [
            'start_date.before_or_equal' => 'La fecha de inicio no puede ser futura',
            'end_date.before_or_equal' => 'La fecha de fin no puede ser futura',
            'end_date.after_or_equal' => 'La fecha de fin no puede ser anterior a la fecha de inicio'
        ]);
        
        // Obtener fechas de filtro
        $startDate = $request->has('start_date') && $request->start_date 
            ? Carbon::parse($request->start_date) 
            : $today;
        
        $endDate = $request->has('end_date') && $request->end_date 
            ? Carbon::parse($request->end_date) 
            : $today;
        
        // Asegurar que no se pueda seleccionar una fecha futura
        if ($startDate->gt($today)) {
            $startDate = $today;
        }
        if ($endDate->gt($today)) {
            $endDate = $today;
        }
        
        // Asegurar que la fecha de inicio no sea posterior a la fecha de fin
        if ($startDate->gt($endDate)) {
            $startDate = $endDate;
        }
        
        // Obtener ventas del rango de fechas
        $periodSales = $this->getPeriodSales($startDate, $endDate);
        
        // Obtener ventas del día anterior para comparación (solo si es un día específico)
        $yesterdaySales = null;
        if ($startDate->eq($endDate)) {
            $yesterday = $startDate->copy()->subDay();
            $yesterdaySales = $this->getDailySales($yesterday);
        }
        
        // Obtener estadísticas del mes de la fecha de inicio
        $monthlyStats = $this->getMonthlyStats($startDate);
        
        return view('daily_sales.index', compact(
            'periodSales', 
            'yesterdaySales', 
            'monthlyStats', 
            'today',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Obtener ventas de un rango de fechas
     */
    private function getPeriodSales($startDate, $endDate)
    {
        $startOfPeriod = $startDate->copy()->startOfDay();
        $endOfPeriod = $endDate->copy()->endOfDay();

        // Ventas de presupuestos convertidos (solo si la tabla existe)
        $quotationSales = 0;
        $countQuotations = 0;
        try {
            if (Schema::hasTable('distributor_quotations')) {
                $quotationSales = DistributorQuotation::where('status', 'active')
                    ->whereBetween('created_at', [$startOfPeriod, $endOfPeriod])
                    ->sum('final_amount');
                $countQuotations = DistributorQuotation::where('status', 'active')
                    ->whereBetween('created_at', [$startOfPeriod, $endOfPeriod])->count();
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
                $technicalRecordSales = DistributorTechnicalRecord::whereBetween('created_at', [$startOfPeriod, $endOfPeriod])
                    ->sum('final_amount');
                $countTechnicalRecords = DistributorTechnicalRecord::whereBetween('created_at', [$startOfPeriod, $endOfPeriod])->count();
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
                $clientAccountSales = ClientCurrentAccount::whereBetween('created_at', [$startOfPeriod, $endOfPeriod])
                    ->sum('amount');
                $countClientAccounts = ClientCurrentAccount::whereBetween('created_at', [$startOfPeriod, $endOfPeriod])->count();
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
                $distributorAccountSales = DistributorCurrentAccount::whereBetween('created_at', [$startOfPeriod, $endOfPeriod])
                    ->sum('amount');
                $countDistributorAccounts = DistributorCurrentAccount::whereBetween('created_at', [$startOfPeriod, $endOfPeriod])->count();
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
     * Mostrar detalle de una categoría específica
     */
    public function showDetail(Request $request, $category)
    {
        $today = Carbon::today();
        
        // Validar fechas de entrada
        $request->validate([
            'start_date' => 'nullable|date|before_or_equal:today',
            'end_date' => 'nullable|date|before_or_equal:today|after_or_equal:start_date'
        ], [
            'start_date.before_or_equal' => 'La fecha de inicio no puede ser futura',
            'end_date.before_or_equal' => 'La fecha de fin no puede ser futura',
            'end_date.after_or_equal' => 'La fecha de fin no puede ser anterior a la fecha de inicio'
        ]);
        
        // Obtener fechas de filtro
        $startDate = $request->has('start_date') && $request->start_date 
            ? Carbon::parse($request->start_date) 
            : $today;
        
        $endDate = $request->has('end_date') && $request->end_date 
            ? Carbon::parse($request->end_date) 
            : $today;
        
        // Asegurar que no se pueda seleccionar una fecha futura
        if ($startDate->gt($today)) {
            $startDate = $today;
        }
        if ($endDate->gt($today)) {
            $endDate = $today;
        }
        
        // Asegurar que la fecha de inicio no sea posterior a la fecha de fin
        if ($startDate->gt($endDate)) {
            $startDate = $endDate;
        }
        
        // Obtener datos detallados según la categoría
        $details = $this->getCategoryDetails($category, $startDate, $endDate);
        
        return view('daily_sales.detail', compact(
            'category', 
            'startDate', 
            'endDate', 
            'details',
            'today'
        ));
    }

    /**
     * Obtener detalles específicos de una categoría
     */
    private function getCategoryDetails($category, $startDate, $endDate)
    {
        $startOfPeriod = $startDate->copy()->startOfDay();
        $endOfPeriod = $endDate->copy()->endOfDay();
        
        switch ($category) {
            case 'quotations':
                return DistributorQuotation::where('status', 'active')
                    ->whereBetween('created_at', [$startOfPeriod, $endOfPeriod])
                    ->with('distributorClient')
                    ->orderBy('created_at', 'desc')
                    ->get();
                    
            case 'technical_records':
                return DistributorTechnicalRecord::whereBetween('created_at', [$startOfPeriod, $endOfPeriod])
                    ->with('distributorClient')
                    ->orderBy('created_at', 'desc')
                    ->get();
                    
            case 'current_accounts':
                $clientAccounts = ClientCurrentAccount::whereBetween('created_at', [$startOfPeriod, $endOfPeriod])
                    ->with('client')
                    ->orderBy('created_at', 'desc')
                    ->get();
                    
                $distributorAccounts = DistributorCurrentAccount::whereBetween('created_at', [$startOfPeriod, $endOfPeriod])
                    ->with('distributorClient')
                    ->orderBy('created_at', 'desc')
                    ->get();
                    
                return [
                    'client_accounts' => $clientAccounts,
                    'distributor_accounts' => $distributorAccounts
                ];
                
            case 'total':
                return [
                    'quotations' => DistributorQuotation::where('status', 'active')
                        ->whereBetween('created_at', [$startOfPeriod, $endOfPeriod])
                        ->with('distributorClient')
                        ->orderBy('created_at', 'desc')
                        ->get(),
                    'technical_records' => DistributorTechnicalRecord::whereBetween('created_at', [$startOfPeriod, $endOfPeriod])
                        ->with('distributorClient')
                        ->orderBy('created_at', 'desc')
                        ->get(),
                    'client_accounts' => ClientCurrentAccount::whereBetween('created_at', [$startOfPeriod, $endOfPeriod])
                        ->with('client')
                        ->orderBy('created_at', 'desc')
                        ->get(),
                    'distributor_accounts' => DistributorCurrentAccount::whereBetween('created_at', [$startOfPeriod, $endOfPeriod])
                        ->with('distributorClient')
                        ->orderBy('created_at', 'desc')
                        ->get()
                ];
                
            default:
                return collect();
        }
    }

    /**
     * Exportar reporte diario a PDF
     */
    public function exportPdf()
    {
        $today = Carbon::today();
        $todaySales = $this->getDailySales($today);
        
        $pdf = Pdf::loadView('daily_sales.pdf', compact('todaySales', 'today'));
        
        return $pdf->download('ventas-dia-' . $today->format('Y-m-d') . '.pdf');
    }
} 