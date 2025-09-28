<?php

namespace App\Http\Controllers;

use App\Models\AfipConfiguration;
use App\Services\AfipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AfipConfigurationController extends Controller
{
    protected $afipService;

    public function __construct(AfipService $afipService)
    {
        $this->afipService = $afipService;
    }

    /**
     * Mostrar configuración de AFIP
     */
    public function index()
    {
        $configurations = AfipConfiguration::orderBy('key')->get();
        
        return view('facturacion.configuration', compact('configurations'));
    }

    /**
     * Actualizar configuración de AFIP
     */
    public function update(Request $request)
    {
        $request->validate([
            'afip_cuit' => 'required|string|size:11',
            'afip_production' => 'boolean',
            'afip_certificate_path' => 'required|string',
            'afip_private_key_path' => 'required|string',
            'afip_point_of_sale' => 'required|string',
            'afip_tax_rate' => 'required|numeric|min:0|max:100'
        ]);

        try {
            // Actualizar configuraciones
            AfipConfiguration::set('afip_cuit', $request->afip_cuit, 'CUIT de la empresa', true);
            AfipConfiguration::set('afip_production', $request->afip_production ? 'true' : 'false', 'Modo producción AFIP');
            AfipConfiguration::set('afip_certificate_path', $request->afip_certificate_path, 'Ruta del certificado AFIP', true);
            AfipConfiguration::set('afip_private_key_path', $request->afip_private_key_path, 'Ruta de la clave privada AFIP', true);
            AfipConfiguration::set('afip_point_of_sale', $request->afip_point_of_sale, 'Punto de venta AFIP');
            AfipConfiguration::set('afip_tax_rate', $request->afip_tax_rate, 'Tasa de IVA por defecto');

            return back()->with('success', 'Configuración actualizada exitosamente');

        } catch (\Exception $e) {
            Log::error('Error actualizando configuración AFIP: ' . $e->getMessage());
            return back()->with('error', 'Error al actualizar configuración: ' . $e->getMessage());
        }
    }

    /**
     * Validar configuración de AFIP
     */
    public function validateConfiguration()
    {
        try {
            $validation = $this->afipService->validateConfiguration();

            if ($validation['valid']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Configuración válida'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuración inválida',
                    'errors' => $validation['errors']
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error validando configuración AFIP: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al validar configuración: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener información del contribuyente
     */
    public function getTaxpayerInfo(Request $request)
    {
        $request->validate([
            'cuit' => 'required|string|size:11'
        ]);

        try {
            $result = $this->afipService->getTaxpayerInfo($request->cuit);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result['data']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error']
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error obteniendo información del contribuyente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener último comprobante autorizado
     */
    public function getLastVoucher(Request $request)
    {
        $request->validate([
            'point_of_sale' => 'required|integer',
            'voucher_type' => 'required|integer'
        ]);

        try {
            $lastNumber = $this->afipService->getLastAuthorizedVoucher(
                $request->point_of_sale,
                $request->voucher_type
            );

            return response()->json([
                'success' => true,
                'last_number' => $lastNumber
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo último comprobante: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener último comprobante: ' . $e->getMessage()
            ]);
        }
    }
}