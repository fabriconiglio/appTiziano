<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AfipConfiguration extends Model
{
    protected $fillable = [
        'key',
        'value',
        'description',
        'is_encrypted'
    ];

    protected $casts = [
        'is_encrypted' => 'boolean'
    ];

    // Métodos para manejar configuraciones
    public static function get($key, $default = null)
    {
        $config = static::where('key', $key)->first();
        
        if (!$config) {
            return $default;
        }

        return $config->is_encrypted ? Crypt::decryptString($config->value) : $config->value;
    }

    public static function set($key, $value, $description = null, $encrypt = false)
    {
        $encryptedValue = $encrypt ? Crypt::encryptString($value) : $value;
        
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $encryptedValue,
                'description' => $description,
                'is_encrypted' => $encrypt
            ]
        );
    }

    public function getDecryptedValueAttribute()
    {
        return $this->is_encrypted ? Crypt::decryptString($this->value) : $this->value;
    }

    // Configuraciones específicas de AFIP
    public static function getAfipConfig()
    {
        return [
            'cuit' => static::get('afip_cuit'),
            'razon_social' => static::get('afip_razon_social', 'TIZIANO'),
            'domicilio_comercial' => static::get('afip_domicilio_comercial', ''),
            'inicio_actividades' => static::get('afip_inicio_actividades', ''),
            'condicion_iva' => static::get('afip_condicion_iva', 'Responsable Inscripto'),
            'production' => static::get('afip_production', 'false') === 'true',
            'certificate_path' => static::get('afip_certificate_path'),
            'private_key_path' => static::get('afip_private_key_path'),
            'point_of_sale' => static::get('afip_point_of_sale', '1'),
            'tax_rate' => static::get('afip_tax_rate', '21.00'),
            'access_token' => static::get('afip_access_token') // Token opcional de AFIP SDK
        ];
    }
}
