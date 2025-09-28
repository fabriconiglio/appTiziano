<?php

namespace App\Services;

use App\Models\AfipConfiguration;
use App\Models\AfipInvoice;
use App\Models\AfipInvoiceItem;
use Afip;
use Exception;
use Illuminate\Support\Facades\Log;

class AfipService
{
    private $afip;
    private $config;

    public function __construct()
    {
        $this->config = AfipConfiguration::getAfipConfig();
        $this->initializeAfip();
    }

    private function initializeAfip()
    {
        try {
            $this->afip = new Afip([
                'CUIT' => $this->config['cuit'],
                'production' => $this->config['production'],
                'cert' => $this->config['certificate_path'],
                'key' => $this->config['private_key_path']
            ]);
        } catch (Exception $e) {
            Log::error('Error inicializando AFIP: ' . $e->getMessage());
            throw new Exception('Error de configuración AFIP: ' . $e->getMessage());
        }
    }

    /**
     * Crear una nueva factura en AFIP
     */
    public function createInvoice(AfipInvoice $invoice): array
    {
        try {
            // Preparar datos para AFIP
            $invoiceData = $this->prepareInvoiceData($invoice);
            
            // Enviar a AFIP
            $response = $this->afip->ElectronicBilling->CreateVoucher($invoiceData);
            
            // Actualizar factura con respuesta
            $invoice->update([
                'cae' => $response['CAE'],
                'cae_expiration' => $response['CAEFchVto'],
                'status' => 'authorized',
                'afip_response' => $response
            ]);

            return [
                'success' => true,
                'cae' => $response['CAE'],
                'expiration' => $response['CAEFchVto'],
                'invoice_number' => $response['voucher_number']
            ];

        } catch (Exception $e) {
            Log::error('Error creando factura AFIP: ' . $e->getMessage());
            
            $invoice->update([
                'status' => 'rejected',
                'afip_response' => ['error' => $e->getMessage()]
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Preparar datos de la factura para AFIP
     */
    private function prepareInvoiceData(AfipInvoice $invoice): array
    {
        $client = $invoice->distributorClient;
        
        // Determinar tipo de comprobante
        $voucherType = $this->getVoucherType($invoice->invoice_type);
        
        // Preparar items
        $items = $this->prepareInvoiceItems($invoice->items);
        
        return [
            'CantReg' => 1,
            'PtoVta' => $invoice->point_of_sale,
            'CbteTipo' => $voucherType,
            'Concepto' => 1, // Productos
            'DocTipo' => $this->getDocumentType($client->document_type),
            'DocNro' => $client->document_number,
            'CbteDesde' => $invoice->invoice_number,
            'CbteHasta' => $invoice->invoice_number,
            'CbteFch' => $invoice->invoice_date->format('Ymd'),
            'ImpTotal' => $invoice->total,
            'ImpTotConc' => 0,
            'ImpNeto' => $invoice->subtotal,
            'ImpOpEx' => 0,
            'ImpTrib' => 0,
            'ImpIVA' => $invoice->tax_amount,
            'FchServDesde' => null,
            'FchServHasta' => null,
            'FchVtoPago' => null,
            'MonId' => 'PES',
            'MonCotiz' => 1,
            'CbtesAsoc' => null,
            'Tributos' => null,
            'Iva' => $items['iva'],
            'Opcionales' => null
        ];
    }

    /**
     * Preparar items de la factura para AFIP
     */
    private function prepareInvoiceItems($items): array
    {
        $ivaItems = [];
        
        foreach ($items as $item) {
            $ivaItems[] = [
                'Id' => 5, // IVA 21%
                'BaseImp' => $item->subtotal,
                'Importe' => $item->tax_amount
            ];
        }

        return ['iva' => $ivaItems];
    }

    /**
     * Obtener tipo de comprobante AFIP
     */
    private function getVoucherType(string $invoiceType): int
    {
        return match($invoiceType) {
            'A' => 1,  // Factura A
            'B' => 6,  // Factura B
            'C' => 11, // Factura C
            default => 6
        };
    }

    /**
     * Obtener tipo de documento AFIP
     */
    private function getDocumentType(string $documentType): int
    {
        return match($documentType) {
            'DNI' => 96,
            'CUIT' => 80,
            'CUIL' => 86,
            'PASAPORTE' => 94,
            default => 96
        };
    }

    /**
     * Obtener último número de comprobante autorizado
     */
    public function getLastAuthorizedVoucher(int $pointOfSale, int $voucherType): int
    {
        try {
            $response = $this->afip->ElectronicBilling->GetLastAuthorizedVoucher([
                'PtoVta' => $pointOfSale,
                'CbteTipo' => $voucherType
            ]);
            
            return $response['CbteNro'] ?? 0;
        } catch (Exception $e) {
            Log::error('Error obteniendo último comprobante: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Verificar estado de un comprobante
     */
    public function checkVoucherStatus(int $pointOfSale, int $voucherType, int $voucherNumber): array
    {
        try {
            $response = $this->afip->ElectronicBilling->GetVoucherInfo([
                'PtoVta' => $pointOfSale,
                'CbteTipo' => $voucherType,
                'CbteNro' => $voucherNumber
            ]);
            
            return [
                'success' => true,
                'data' => $response
            ];
        } catch (Exception $e) {
            Log::error('Error verificando comprobante: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener información del contribuyente
     */
    public function getTaxpayerInfo(string $cuit): array
    {
        try {
            $response = $this->afip->RegisterScopeFive->GetTaxpayer([
                'Cuit' => $cuit
            ]);
            
            return [
                'success' => true,
                'data' => $response
            ];
        } catch (Exception $e) {
            Log::error('Error obteniendo información del contribuyente: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validar configuración AFIP
     */
    public function validateConfiguration(): array
    {
        $errors = [];
        
        if (empty($this->config['cuit'])) {
            $errors[] = 'CUIT no configurado';
        }
        
        if (empty($this->config['certificate_path'])) {
            $errors[] = 'Ruta del certificado no configurada';
        }
        
        if (empty($this->config['private_key_path'])) {
            $errors[] = 'Ruta de la clave privada no configurada';
        }
        
        if (!empty($errors)) {
            return [
                'valid' => false,
                'errors' => $errors
            ];
        }
        
        try {
            // Intentar inicializar AFIP
            $this->initializeAfip();
            return ['valid' => true];
        } catch (Exception $e) {
            return [
                'valid' => false,
                'errors' => ['Error de configuración: ' . $e->getMessage()]
            ];
        }
    }
}
