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
            $taFolder = storage_path('app/afip/ta/');
            if (!is_dir($taFolder)) {
                mkdir($taFolder, 0755, true);
            }
            
            // Leer contenido de certificados (versión 1.2.x)
            $certContent = file_get_contents($cert);
            $keyContent = file_get_contents($key);
            
            // Preparar opciones para el constructor de Afip (versión 1.2.x)
            // Esta versión usa la API de AFIP SDK
            $afipOptions = [
                'CUIT' => $this->config['cuit'],
                'production' => $this->config['production'],
                'cert' => $certContent,  // Contenido del certificado
                'key' => $keyContent,    // Contenido de la clave
                'ta_folder' => $taFolder
            ];
            
            // Agregar access_token (requerido en versión 1.2.x)
            if (!empty($this->config['access_token'])) {
                $afipOptions['access_token'] = $this->config['access_token'];
            }
            
            Log::info('Inicializando AFIP SDK v1.2.x', [
                'cert' => basename($cert),
                'key' => basename($key),
                'production' => $this->config['production']
            ]);
            
            $this->afip = new Afip($afipOptions);
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
        
        // Determinar tipo y número de documento según tipo de factura
        $docTipo = 99; // Sin documento por defecto
        $docNro = 0;
        $condicionIva = 5; // Consumidor Final por defecto
        
        if ($invoice->invoice_type === 'A') {
            // Factura A: receptor debe ser Responsable Inscripto
            // Requiere CUIT del cliente
            if (!empty($client->cuit)) {
                $docTipo = $this->getDocumentType('CUIT');
                $docNro = intval(str_replace(['-', ' '], '', $client->cuit));
                $condicionIva = 1; // Responsable Inscripto
            } elseif (!empty($client->dni)) {
                // Si no tiene CUIT pero tiene DNI, usar DNI
                $docTipo = $this->getDocumentType('DNI');
                $docNro = intval($client->dni);
                $condicionIva = 1; // Responsable Inscripto
            }
        } else {
            // Factura B: receptor es Consumidor Final, Monotributista, Exento, etc.
            $condicionIva = 5; // Consumidor Final
            if (!empty($client->dni)) {
                $docTipo = $this->getDocumentType('DNI');
                $docNro = intval($client->dni);
            }
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
            'PtoVta' => $data['PtoVta'],
            'CbteTipo' => $data['CbteTipo'],
            'docTipo' => $docTipo,
            'docNro' => $docNro,
            'condicionIva' => $condicionIva,
            'ImpTotal' => $data['ImpTotal'],
            'ImpNeto' => $data['ImpNeto'],
            'ImpIVA' => $data['ImpIVA'],
            'CUIT' => $this->config['cuit'],
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
            $response = $this->afip->RegisterScopeFive->GetTaxpayerDetails($cuit);
            
            if ($response === null) {
                return [
                    'success' => false,
                    'error' => 'El contribuyente no existe en AFIP'
                ];
            }
            
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
