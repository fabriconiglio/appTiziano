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
    public $afip;
    private $config;

    public function __construct()
    {
        $this->config = AfipConfiguration::getAfipConfig();
        $this->initializeAfip();
    }

    private function initializeAfip()
    {
        try {
            $cert = $this->config['certificate_path'];
            $key  = $this->config['private_key_path'];

            // Validar que los archivos existan y sean legibles
            if (!file_exists($cert)) {
                throw new Exception("Certificado no encontrado en: {$cert}");
            }
            if (!is_readable($cert)) {
                throw new Exception("Certificado no accesible en: {$cert}");
            }
            if (!file_exists($key)) {
                throw new Exception("Clave privada no encontrada en: {$key}");
            }
            if (!is_readable($key)) {
                throw new Exception("Clave privada no accesible en: {$key}");
            }

            // Validar que el certificado sea PEM válido
            $certContent = file_get_contents($cert);
            $certContent = trim($certContent);
            
            // Validar cabecera y pie del certificado
            if (!str_starts_with($certContent, '-----BEGIN CERTIFICATE-----')) {
                throw new Exception("El certificado no es PEM válido (cabecera faltante).");
            }
            if (!str_ends_with($certContent, '-----END CERTIFICATE-----')) {
                throw new Exception("El certificado no es PEM válido (pie faltante).");
            }

            // Validar que el certificado sea parseable
            $certResource = openssl_x509_read($certContent);
            if ($certResource === false) {
                throw new Exception("El certificado no es válido (error al parsear).");
            }

            // Obtener información del certificado
            $certInfo = openssl_x509_parse($certResource);
            if ($certInfo === false) {
                throw new Exception("No se pudo obtener información del certificado.");
            }

            // Validar que el certificado no esté vencido
            $validTo = $certInfo['validTo_time_t'];
            if ($validTo < time()) {
                throw new Exception("El certificado está vencido (expiró el " . date('Y-m-d', $validTo) . ").");
            }

            // Validar que el certificado corresponda al ambiente correcto
            $issuer = $certInfo['issuer']['CN'] ?? '';
            $isProduction = $this->config['production'];
            
            if ($isProduction && str_contains($issuer, 'Test')) {
                throw new Exception("El certificado es de TESTING pero la configuración está en PRODUCCIÓN.");
            }
            if (!$isProduction && !str_contains($issuer, 'Test')) {
                Log::warning("El certificado parece ser de PRODUCCIÓN pero la configuración está en TESTING.");
            }

            // Log de información del certificado
            Log::info('Certificado AFIP validado correctamente', [
                'issuer' => $issuer,
                'subject' => $certInfo['subject']['CN'] ?? 'N/A',
                'valid_until' => date('Y-m-d', $validTo),
                'environment' => $isProduction ? 'PRODUCCIÓN' : 'TESTING'
            ]);

            // Asegurar que existe el directorio para tokens de autorización
            $taFolder = storage_path('app/afip/ta');
            if (!is_dir($taFolder)) {
                mkdir($taFolder, 0755, true);
            }

            // Leer el contenido de los certificados
            $certContent = file_get_contents($cert);
            $keyContent = file_get_contents($key);
            
            $this->afip = new Afip([
                'CUIT' => $this->config['cuit'],
                'production' => $this->config['production'],
                'cert' => $certContent,  // Pasar contenido en lugar de ruta
                'key' => $keyContent,    // Pasar contenido en lugar de ruta
                'ta_folder' => $taFolder
            ]);
        } catch (Exception $e) {
            Log::error('Error inicializando AFIP: ' . $e->getMessage(), [
                'certificate_path' => $cert ?? 'N/A',
                'private_key_path' => $key ?? 'N/A',
                'cuit' => $this->config['cuit'] ?? 'N/A',
                'production' => $this->config['production'] ?? 'N/A'
            ]);
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
                'invoice_number' => $response['voucher_number'] ?? $invoice->invoice_number
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
        
        // Determinar tipo y número de documento
        $docTipo = 99; // Consumidor Final por defecto
        $docNro = 0;
        $condicionIva = 5; // Consumidor Final por defecto
        
        if (!empty($client->dni)) {
            // Si tiene DNI, usar tipo DNI (96) con el número
            $docTipo = $this->getDocumentType('DNI');
            $docNro = intval($client->dni);
            // Si tiene DNI, asumimos Responsable Inscripto (1)
            $condicionIva = 1;
        }
        
        // Para facturas tipo C, el IVA debe ser 0 y no se envía el objeto Iva
        $impIVA = ($invoice->invoice_type === 'C') ? 0 : $invoice->tax_amount;
        $impNeto = ($invoice->invoice_type === 'C') ? $invoice->total : $invoice->subtotal;
        $ivaArray = ($invoice->invoice_type === 'C') ? null : $items['iva'];
        
        $data = [
            'CantReg' => 1,
            'PtoVta' => $invoice->point_of_sale,
            'CbteTipo' => $voucherType,
            'Concepto' => 1, // Productos
            'DocTipo' => $docTipo,
            'DocNro' => $docNro,
            'CbteDesde' => $invoice->invoice_number,
            'CbteHasta' => $invoice->invoice_number,
            'CbteFch' => $invoice->invoice_date->format('Ymd'),
            'ImpTotal' => $invoice->total,
            'ImpTotConc' => 0,
            'ImpNeto' => $impNeto,
            'ImpOpEx' => 0,
            'ImpTrib' => 0,
            'ImpIVA' => $impIVA,
            'FchServDesde' => null,
            'FchServHasta' => null,
            'FchVtoPago' => null,
            'MonId' => 'PES',
            'MonCotiz' => 1,
            'CbtesAsoc' => null,
            'Tributos' => null,
            'Iva' => $ivaArray,
            'Opcionales' => null,
            'CondicionIVAReceptorId' => $condicionIva // Condición Frente al IVA del receptor
        ];
        
        // Log para debug
        Log::info('Datos enviados a AFIP', [
            'docTipo' => $docTipo,
            'docNro' => $docNro,
            'condicionIva' => $condicionIva,
            'opcionales' => $data['Opcionales']
        ]);
        
        return $data;
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
            $response = $this->afip->ElectronicBilling->GetLastVoucher($pointOfSale, $voucherType);
            
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
