<?php

namespace Database\Seeders;

use App\Models\AfipConfiguration;
use Illuminate\Database\Seeder;

class AfipConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Configuraciones por defecto para AFIP
        AfipConfiguration::set('afip_cuit', '', 'CUIT de la empresa', true);
        AfipConfiguration::set('afip_production', 'false', 'Modo producción AFIP');
        AfipConfiguration::set('afip_certificate_path', '', 'Ruta del certificado AFIP', true);
        AfipConfiguration::set('afip_private_key_path', '', 'Ruta de la clave privada AFIP', true);
        AfipConfiguration::set('afip_point_of_sale', '1', 'Punto de venta AFIP');
        AfipConfiguration::set('afip_tax_rate', '21.00', 'Tasa de IVA por defecto');
    }
}