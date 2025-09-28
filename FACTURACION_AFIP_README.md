# Módulo de Facturación AFIP

Este módulo permite la integración completa con los servicios web de AFIP para la emisión de facturas electrónicas.

## Características

- ✅ Emisión de facturas A, B y C
- ✅ Integración con SDK oficial de AFIP
- ✅ Gestión de clientes distribuidores
- ✅ Cálculo automático de IVA
- ✅ Estados de factura (Borrador, Enviada, Autorizada, Rechazada, Cancelada)
- ✅ Configuración segura de certificados
- ✅ Interfaz web completa

## Instalación

### 1. Dependencias

El módulo utiliza el SDK oficial de AFIP:

```bash
composer require afipsdk/afip.php
```

### 2. Migraciones

Las migraciones ya han sido ejecutadas:

```bash
php artisan migrate
```

### 3. Configuración

#### Variables de Entorno

Agregar al archivo `.env`:

```env
# Configuración AFIP
AFIP_CUIT=20123456789
AFIP_PRODUCTION=false
AFIP_CERTIFICATE_PATH=/path/to/certificate.crt
AFIP_PRIVATE_KEY_PATH=/path/to/private.key
AFIP_POINT_OF_SALE=1
AFIP_TAX_RATE=21.00
```

#### Certificados AFIP

1. Obtener certificados de AFIP (testing o producción)
2. Colocar archivos en directorio seguro del servidor
3. Configurar rutas en variables de entorno
4. Asegurar permisos de lectura para la aplicación

## Uso

### 1. Configuración Inicial

1. Ir a **Facturación AFIP > Configuración**
2. Completar datos de la empresa:
   - CUIT
   - Punto de venta
   - Tasa de IVA
   - Rutas de certificados
3. Validar configuración

### 2. Crear Factura

1. Ir a **Facturación AFIP > Nueva Factura**
2. Seleccionar cliente distribuidor
3. Elegir tipo de factura (A, B, C)
4. Agregar productos con cantidades y precios
5. Revisar totales automáticos
6. Guardar factura

### 3. Enviar a AFIP

1. Desde el listado de facturas, hacer clic en "Enviar a AFIP"
2. El sistema enviará la factura a AFIP
3. Si es exitosa, se obtendrá el CAE (Código de Autorización Electrónica)
4. La factura quedará en estado "Autorizada"

## Estructura del Módulo

### Modelos

- **AfipInvoice**: Facturas principales
- **AfipInvoiceItem**: Items de cada factura
- **AfipConfiguration**: Configuraciones del sistema

### Controladores

- **AfipInvoiceController**: Gestión de facturas
- **AfipConfigurationController**: Configuración del sistema

### Servicios

- **AfipService**: Integración con SDK de AFIP

### Vistas

- `facturacion/index.blade.php`: Listado de facturas
- `facturacion/create.blade.php`: Crear nueva factura
- `facturacion/show.blade.php`: Ver factura específica
- `facturacion/configuration.blade.php`: Configuración AFIP

## Estados de Factura

- **Borrador**: Factura creada, no enviada a AFIP
- **Enviada**: Enviada a AFIP, esperando respuesta
- **Autorizada**: Aprobada por AFIP con CAE válido
- **Rechazada**: Rechazada por AFIP
- **Cancelada**: Cancelada por el usuario

## Tipos de Factura

- **Factura A**: Para consumidores finales con CUIT
- **Factura B**: Para consumidores finales sin CUIT
- **Factura C**: Para exportaciones

## Seguridad

- Los certificados se almacenan encriptados
- Las claves privadas no se exponen en logs
- Validación de permisos en todas las operaciones
- Sanitización de datos de entrada

## Troubleshooting

### Error de Certificado

```
Error: No se puede leer el certificado
```

**Solución**: Verificar que la ruta del certificado sea correcta y tenga permisos de lectura.

### Error de CUIT

```
Error: CUIT inválido
```

**Solución**: Verificar que el CUIT tenga 11 dígitos y sea válido.

### Error de Conexión AFIP

```
Error: No se puede conectar a AFIP
```

**Solución**: 
1. Verificar conectividad a internet
2. Verificar que los certificados sean válidos
3. Verificar que no esté en modo producción sin certificados de producción

## Logs

Los logs de AFIP se almacenan en:
- `storage/logs/laravel.log`
- Buscar por "AFIP" para filtrar logs específicos

## Soporte

Para problemas específicos del módulo:
1. Revisar logs de Laravel
2. Verificar configuración de AFIP
3. Validar certificados con AFIP
4. Contactar al administrador del sistema

## Desarrollo

### Agregar Nuevo Tipo de Factura

1. Actualizar `config/afip.php`
2. Modificar `AfipService::getVoucherType()`
3. Actualizar vistas de creación
4. Agregar validaciones necesarias

### Personalizar Cálculos

1. Modificar `AfipInvoiceItem::calculateSubtotal()`
2. Actualizar lógica de IVA en `AfipService`
3. Ajustar validaciones en controladores

## Notas Importantes

- ⚠️ **Modo Producción**: Las facturas son reales y tienen validez fiscal
- ⚠️ **Certificados**: Deben renovarse antes del vencimiento
- ⚠️ **CAE**: Tiene fecha de vencimiento, verificar antes de usar
- ⚠️ **Backup**: Realizar respaldos regulares de la configuración
