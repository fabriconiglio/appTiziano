<?php

namespace App\Http\Controllers;

use App\Models\ClientCurrentAccount;
use App\Models\TechnicalRecord;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\ClienteNoFrecuente;
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
        
        $yesterday = $startDate->copy()->subDay();
        
        // Obtener ventas del período seleccionado
        $todaySales = $this->getPeriodSales($startDate, $endDate);
        
        // Obtener ventas del día anterior para comparación
        $yesterdaySales = $this->getDailySales($yesterday);
        
        // Obtener estadísticas del mes de la fecha de inicio
        $monthlyStats = $this->getMonthlyStats($startDate);
        
        // Obtener servicios más populares del período
        $popularServices = $this->getPopularServices($startDate, $endDate);
        
        // Obtener productos más vendidos del período
        $popularProducts = $this->getPopularProducts($startDate, $endDate);
        
        return view('hairdressing_daily_sales.index', compact(
            'todaySales', 
            'yesterdaySales', 
            'monthlyStats',
            'popularServices',
            'popularProducts',
            'today',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Obtener IDs de servicios registrados como cuenta corriente en un período específico
     * Filtra por la fecha del servicio, no por la fecha de creación de la cuenta corriente
     */
    private function getTechnicalRecordsInCurrentAccount($startDate = null, $endDate = null)
    {
        $query = ClientCurrentAccount::whereNotNull('technical_record_id')
            ->where('type', 'debt') // Solo deudas, no pagos
            ->join('technical_records', 'client_current_accounts.technical_record_id', '=', 'technical_records.id');
        
        if ($startDate && $endDate) {
            // Filtrar por la fecha del servicio, no por la fecha de creación de la cuenta corriente
            $query->whereBetween('technical_records.service_date', [
                $startDate->copy()->startOfDay(),
                $endDate->copy()->endOfDay()
            ]);
        }
        
        return $query->pluck('client_current_accounts.technical_record_id')->toArray();
    }

    /**
     * Obtener ventas de un período específico para peluquería
     */
    private function getPeriodSales($startDate, $endDate)
    {
        $startOfPeriod = $startDate->copy()->startOfDay();
        $endOfPeriod = $endDate->copy()->endOfDay();

        // Ventas de cuentas corrientes de clientes (deudas)
        $clientAccountSales = ClientCurrentAccount::where('type', 'debt')
            ->whereBetween('created_at', [$startOfPeriod, $endOfPeriod])
            ->sum('amount');

        // Pagos recibidos de cuentas corrientes (CC pagas)
        $clientAccountPayments = ClientCurrentAccount::where('type', 'payment')
            ->whereBetween('created_at', [$startOfPeriod, $endOfPeriod])
            ->sum('amount');

        // Ventas de fichas técnicas (costo real del servicio)
        // Excluir servicios registrados como cuenta corriente en este período
        $technicalRecordsInCC = $this->getTechnicalRecordsInCurrentAccount($startDate, $endDate);
        $technicalRecordSales = TechnicalRecord::whereBetween('service_date', [$startOfPeriod, $endOfPeriod])
            ->whereNotIn('id', $technicalRecordsInCC)
            ->sum('service_cost');

        // Ventas de productos vendidos (estimación basada en salidas de stock)
        $productSales = StockMovement::where('type', 'salida')
            ->whereBetween('stock_movements.created_at', [$startOfPeriod, $endOfPeriod])
            ->join('products', 'stock_movements.product_id', '=', 'products.id')
            ->sum(DB::raw('stock_movements.quantity * COALESCE(products.price, 0)'));

        // Servicios adicionales ya están incluidos en service_cost, no duplicar
        $additionalServices = 0;

        // Ventas de clientes no frecuentes
        $clienteNoFrecuenteSales = ClienteNoFrecuente::whereBetween('fecha', [$startOfPeriod, $endOfPeriod])
            ->sum('monto');

        // Total solo incluye: CC Pagas + Servicios + Productos + Clientes No Frecuentes
        // NO incluye las deudas de CC (clientAccountSales) porque ese dinero aún no se recibió
        $totalSales = $clientAccountPayments + $technicalRecordSales + $productSales + $additionalServices + $clienteNoFrecuenteSales;

        return [
            'total' => $totalSales,
            'client_accounts' => $clientAccountSales,
            'client_accounts_payments' => $clientAccountPayments,
            'technical_records' => $technicalRecordSales,
            'product_sales' => $productSales,
            'additional_services' => $additionalServices,
            'cliente_no_frecuente_sales' => $clienteNoFrecuenteSales,
            'count_client_accounts' => ClientCurrentAccount::where('type', 'debt')
                ->whereBetween('created_at', [$startOfPeriod, $endOfPeriod])->count(),
            'count_client_accounts_payments' => ClientCurrentAccount::where('type', 'payment')
                ->whereBetween('created_at', [$startOfPeriod, $endOfPeriod])->count(),
            'count_technical_records' => TechnicalRecord::whereBetween('service_date', [$startOfPeriod, $endOfPeriod])
                ->whereNotIn('id', $technicalRecordsInCC)
                ->count(),
            'count_product_sales' => StockMovement::where('type', 'salida')
                ->whereBetween('stock_movements.created_at', [$startOfPeriod, $endOfPeriod])->count(),
            'count_cliente_no_frecuente' => ClienteNoFrecuente::whereBetween('fecha', [$startOfPeriod, $endOfPeriod])->count(),
        ];
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

        // Pagos recibidos de cuentas corrientes (CC pagas)
        $clientAccountPayments = ClientCurrentAccount::where('type', 'payment')
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->sum('amount');

        // Ventas de fichas técnicas (costo real del servicio)
        // Excluir servicios registrados como cuenta corriente en este día
        $technicalRecordsInCC = $this->getTechnicalRecordsInCurrentAccount($date, $date);
        $technicalRecordSales = TechnicalRecord::whereBetween('service_date', [$startOfDay, $endOfDay])
            ->whereNotIn('id', $technicalRecordsInCC)
            ->sum('service_cost');

        // Ventas de productos vendidos (estimación basada en salidas de stock)
        $productSales = StockMovement::where('type', 'salida')
            ->whereBetween('stock_movements.created_at', [$startOfDay, $endOfDay])
            ->join('products', 'stock_movements.product_id', '=', 'products.id')
            ->sum(DB::raw('stock_movements.quantity * COALESCE(products.price, 0)'));

        // Servicios adicionales ya están incluidos en service_cost, no duplicar
        $additionalServices = 0;

        // Ventas de clientes no frecuentes
        $clienteNoFrecuenteSales = ClienteNoFrecuente::whereDate('fecha', $date)
            ->sum('monto');

        // Total solo incluye: CC Pagas + Servicios + Productos + Clientes No Frecuentes
        // NO incluye las deudas de CC (clientAccountSales) porque ese dinero aún no se recibió
        $totalSales = $clientAccountPayments + $technicalRecordSales + $productSales + $additionalServices + $clienteNoFrecuenteSales;

        return [
            'total' => $totalSales,
            'client_accounts' => $clientAccountSales,
            'client_accounts_payments' => $clientAccountPayments,
            'technical_records' => $technicalRecordSales,
            'product_sales' => $productSales,
            'additional_services' => $additionalServices,
            'cliente_no_frecuente_sales' => $clienteNoFrecuenteSales,
            'count_client_accounts' => ClientCurrentAccount::where('type', 'debt')
                ->whereBetween('created_at', [$startOfDay, $endOfDay])->count(),
            'count_client_accounts_payments' => ClientCurrentAccount::where('type', 'payment')
                ->whereBetween('created_at', [$startOfDay, $endOfDay])->count(),
            'count_technical_records' => TechnicalRecord::whereBetween('service_date', [$startOfDay, $endOfDay])
                ->whereNotIn('id', $technicalRecordsInCC)
                ->count(),
            'count_product_sales' => StockMovement::where('type', 'salida')
                ->whereBetween('stock_movements.created_at', [$startOfDay, $endOfDay])->count(),
            'count_cliente_no_frecuente' => ClienteNoFrecuente::whereDate('fecha', $date)->count(),
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

        $monthlyClientAccountPayments = ClientCurrentAccount::where('type', 'payment')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        // Excluir servicios registrados como cuenta corriente en este mes
        $technicalRecordsInCC = $this->getTechnicalRecordsInCurrentAccount($startOfMonth, $endOfMonth);
        $monthlyTechnicalRecords = TechnicalRecord::whereBetween('service_date', [$startOfMonth, $endOfMonth])
            ->whereNotIn('id', $technicalRecordsInCC)
            ->sum('service_cost');

        $monthlyProductSales = StockMovement::where('type', 'salida')
            ->whereBetween('stock_movements.created_at', [$startOfMonth, $endOfMonth])
            ->join('products', 'stock_movements.product_id', '=', 'products.id')
            ->sum(DB::raw('stock_movements.quantity * COALESCE(products.price, 0)'));

        $monthlyAdditionalServices = TechnicalRecord::whereBetween('service_date', [$startOfMonth, $endOfMonth])
            ->whereNotIn('id', $technicalRecordsInCC)
            ->sum('service_cost') * 0.5; // 50% del costo total como servicios adicionales

        // Total mensual solo incluye: CC Pagas + Servicios + Productos
        // NO incluye las deudas de CC (monthlyClientAccounts) porque ese dinero aún no se recibió
        return [
            'total' => $monthlyClientAccountPayments + $monthlyTechnicalRecords + $monthlyProductSales + $monthlyAdditionalServices,
            'client_accounts' => $monthlyClientAccounts,
            'client_accounts_payments' => $monthlyClientAccountPayments,
            'technical_records' => $monthlyTechnicalRecords,
            'product_sales' => $monthlyProductSales,
            'additional_services' => $monthlyAdditionalServices,
        ];
    }



    /**
     * Obtener servicios más populares del período
     */
    private function getPopularServices($startDate, $endDate = null)
    {
        if ($endDate === null) {
            // Si solo se pasa una fecha, usar el método original
            $startOfDay = $startDate->copy()->startOfDay();
            $endOfDay = $startDate->copy()->endOfDay();
        } else {
            $startOfDay = $startDate->copy()->startOfDay();
            $endOfDay = $endDate->copy()->endOfDay();
        }

        // Excluir servicios registrados como cuenta corriente
        $technicalRecordsInCC = $this->getTechnicalRecordsInCurrentAccount($startDate, $endDate);
        return TechnicalRecord::whereBetween('service_date', [$startOfDay, $endOfDay])
            ->whereNotIn('id', $technicalRecordsInCC)
            ->select('hair_treatments', 'service_type', DB::raw('count(*) as total'), DB::raw('sum(service_cost) as total_cost'))
            ->whereNotNull('hair_treatments')
            ->groupBy('hair_treatments', 'service_type')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Obtener productos más vendidos del período
     */
    private function getPopularProducts($startDate, $endDate = null)
    {
        if ($endDate === null) {
            // Si solo se pasa una fecha, usar el método original
            $startOfDay = $startDate->copy()->startOfDay();
            $endOfDay = $startDate->copy()->endOfDay();
        } else {
            $startOfDay = $startDate->copy()->startOfDay();
            $endOfDay = $endDate->copy()->endOfDay();
        }

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
     * Mostrar detalles de una categoría específica
     */
    public function detail(Request $request)
    {
        $today = Carbon::today();
        
        // Validar fechas de entrada
        $request->validate([
            'start_date' => 'nullable|date|before_or_equal:today',
            'end_date' => 'nullable|date|before_or_equal:today|after_or_equal:start_date',
            'category' => 'required|string|in:total,client_accounts,client_accounts_payments,technical_records,product_sales,cliente_no_frecuente,forma_pago_efectivo,forma_pago_tarjeta,forma_pago_transferencia,forma_pago_deudor'
        ], [
            'start_date.before_or_equal' => 'La fecha de inicio no puede ser futura',
            'end_date.before_or_equal' => 'La fecha de fin no puede ser futura',
            'end_date.after_or_equal' => 'La fecha de fin no puede ser anterior a la fecha de inicio',
            'category.required' => 'La categoría es requerida',
            'category.in' => 'La categoría seleccionada no es válida'
        ]);
        
        $startDate = $request->has('start_date') && $request->start_date 
            ? Carbon::parse($request->start_date) 
            : $today;
        
        $endDate = $request->has('end_date') && $request->end_date 
            ? Carbon::parse($request->end_date) 
            : $today;
        
        $category = $request->get('category');
        
        // Obtener datos según la categoría
        $data = $this->getCategoryDetail($category, $startDate, $endDate);
        
        return view('hairdressing_daily_sales.detail', compact(
            'data', 
            'category', 
            'startDate', 
            'endDate', 
            'today'
        ));
    }

    /**
     * Obtener detalles de una categoría específica
     */
    private function getCategoryDetail($category, $startDate, $endDate)
    {
        $startOfPeriod = $startDate->copy()->startOfDay();
        $endOfPeriod = $endDate->copy()->endOfDay();
        
        switch ($category) {
            case 'total':
                // Para el total, devolvemos un resumen de todas las categorías
                return collect([
                    'client_accounts' => ClientCurrentAccount::where('type', 'debt')
                        ->whereBetween('created_at', [$startOfPeriod, $endOfPeriod])
                        ->with('client')
                        ->orderBy('created_at', 'desc')
                        ->get(),
                    'client_accounts_payments' => ClientCurrentAccount::where('type', 'payment')
                        ->whereBetween('created_at', [$startOfPeriod, $endOfPeriod])
                        ->with('client')
                        ->orderBy('created_at', 'desc')
                        ->get(),
                    'technical_records' => TechnicalRecord::whereBetween('service_date', [$startOfPeriod, $endOfPeriod])
                        ->whereNotIn('id', function($query) {
                            $query->select('technical_record_id')
                                ->from('client_current_accounts')
                                ->whereNotNull('technical_record_id');
                        })
                        ->with('client')
                        ->orderBy('service_date', 'desc')
                        ->get(),
                    'product_sales' => StockMovement::where('type', 'salida')
                        ->whereBetween('stock_movements.created_at', [$startOfPeriod, $endOfPeriod])
                        ->join('products', 'stock_movements.product_id', '=', 'products.id')
                        ->select('stock_movements.*', 'products.name as product_name', 'products.price')
                        ->orderBy('stock_movements.created_at', 'desc')
                        ->get(),
                    'cliente_no_frecuente' => ClienteNoFrecuente::whereBetween('fecha', [$startOfPeriod, $endOfPeriod])
                        ->with('user')
                        ->orderBy('fecha', 'desc')
                        ->get()
                ]);
                    
            case 'client_accounts':
                return ClientCurrentAccount::where('type', 'debt')
                    ->whereBetween('created_at', [$startOfPeriod, $endOfPeriod])
                    ->with('client')
                    ->orderBy('created_at', 'desc')
                    ->get();
                    
            case 'client_accounts_payments':
                return ClientCurrentAccount::where('type', 'payment')
                    ->whereBetween('created_at', [$startOfPeriod, $endOfPeriod])
                    ->with('client')
                    ->orderBy('created_at', 'desc')
                    ->get();
                    
            case 'technical_records':
                return TechnicalRecord::whereBetween('service_date', [$startOfPeriod, $endOfPeriod])
                    ->whereNotIn('id', function($query) {
                        $query->select('technical_record_id')
                            ->from('client_current_accounts')
                            ->whereNotNull('technical_record_id');
                    })
                    ->with('client')
                    ->orderBy('service_date', 'desc')
                    ->get();
                    
            case 'product_sales':
                return StockMovement::where('type', 'salida')
                    ->whereBetween('stock_movements.created_at', [$startOfPeriod, $endOfPeriod])
                    ->join('products', 'stock_movements.product_id', '=', 'products.id')
                    ->select('stock_movements.*', 'products.name as product_name', 'products.price')
                    ->orderBy('stock_movements.created_at', 'desc')
                    ->get();
                    
            case 'cliente_no_frecuente':
                return ClienteNoFrecuente::whereBetween('fecha', [$startOfPeriod, $endOfPeriod])
                    ->with('user')
                    ->orderBy('fecha', 'desc')
                    ->get();
                    
            case 'forma_pago_efectivo':
            case 'forma_pago_tarjeta':
            case 'forma_pago_transferencia':
            case 'forma_pago_deudor':
                // Extraer el método de pago de la categoría
                $formaPago = str_replace('forma_pago_', '', $category);
                // Mapear deudor a deuda para fichas técnicas
                $paymentMethodFT = $formaPago === 'deudor' ? 'deuda' : $formaPago;
                
                // Obtener IDs de fichas técnicas en CC
                $technicalRecordsInCC = $this->getTechnicalRecordsInCurrentAccount($startDate, $endDate);
                
                // Fichas técnicas con este método de pago
                $fichasTecnicas = TechnicalRecord::whereBetween('service_date', [$startOfPeriod, $endOfPeriod])
                    ->whereNotIn('id', $technicalRecordsInCC)
                    ->where('payment_method', $paymentMethodFT)
                    ->with('client')
                    ->orderBy('service_date', 'desc')
                    ->get();
                
                // Clientes no frecuentes con este método de pago
                $clientesNoFrecuentes = ClienteNoFrecuente::whereBetween('fecha', [$startOfPeriod, $endOfPeriod])
                    ->where('forma_pago', $formaPago)
                    ->with('user')
                    ->orderBy('fecha', 'desc')
                    ->get();
                
                return collect([
                    'forma_pago' => $formaPago,
                    'technical_records' => $fichasTecnicas,
                    'cliente_no_frecuente' => $clientesNoFrecuentes
                ]);
                    
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
        $popularServices = $this->getPopularServices($today);
        $popularProducts = $this->getPopularProducts($today);
        
        $pdf = Pdf::loadView('hairdressing_daily_sales.pdf', compact(
            'todaySales', 
            'popularServices', 
            'popularProducts', 
            'today'
        ));
        
        return $pdf->download('ventas-peluqueria-dia-' . $today->format('Y-m-d') . '.pdf');
    }
} 