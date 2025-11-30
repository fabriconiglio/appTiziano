# Actualización de Certificados AFIP del Dueño

## Estado de la Actualización

### ✅ Completado

1. **Certificados copiados** a `storage/app/afip/certificates/`:
   - `wsfe_prod_75e43124c3be273f.crt` (certificado de producción)
   - `clave_privada_paola.key` (clave privada)

2. **Permisos establecidos** (644) para ambos archivos

3. **Comando Artisan creado** para actualizar la configuración:
   - `php artisan afip:update-certificates`

### ⏳ Pendiente (requiere base de datos)

1. Actualizar CUIT a `27257574704` en la base de datos
2. Actualizar rutas de certificados en la base de datos
3. Verificar que la configuración es válida

## Instrucciones para Completar la Actualización

### Opción 1: Usar el comando Artisan (Recomendado)

Cuando la base de datos esté disponible, ejecutar:

```bash
cd /home/fabrizzio/Escritorio/appTiziano
php artisan afip:update-certificates
```

Este comando:
- Actualizará el CUIT a `27257574704`
- Actualizará las rutas de certificado y clave privada
- Verificará que los archivos existen y son válidos
- Mostrará advertencias si el modo producción no coincide

### Opción 2: Usar el script PHP

Alternativamente, puedes ejecutar:

```bash
cd /home/fabrizzio/Escritorio/appTiziano
php update_afip_certificates.php
```

### Opción 3: Actualizar manualmente desde la interfaz web

1. Iniciar el servidor de desarrollo:
   ```bash
   php artisan serve
   ```

2. Ir a la configuración de AFIP en el navegador:
   - Ruta: `/facturacion/configuration`

3. Actualizar los siguientes campos:
   - **CUIT:** `27257574704`
   - **Ruta del Certificado:** `/home/fabrizzio/Escritorio/appTiziano/storage/app/afip/certificates/wsfe_prod_75e43124c3be273f.crt`
   - **Ruta de la Clave Privada:** `/home/fabrizzio/Escritorio/appTiziano/storage/app/afip/certificates/clave_privada_paola.key`

4. Verificar el modo producción (el certificado parece ser de producción según el nombre)

5. Hacer clic en "Guardar Configuración"

6. Hacer clic en "Validar Configuración" para verificar que todo está correcto

## Verificación Post-Actualización

Después de actualizar la configuración, verificar:

1. **Certificados válidos:**
   ```bash
   php artisan afip:test-connection
   ```

2. **Verificar configuración en la base de datos:**
   ```bash
   php artisan tinker
   ```
   Luego ejecutar:
   ```php
   use App\Models\AfipConfiguration;
   echo "CUIT: " . AfipConfiguration::get('afip_cuit') . "\n";
   echo "Certificado: " . AfipConfiguration::get('afip_certificate_path') . "\n";
   echo "Clave privada: " . AfipConfiguration::get('afip_private_key_path') . "\n";
   ```

## Notas Importantes

- ⚠️ El certificado `wsfe_prod_75e43124c3be273f.crt` parece ser de **PRODUCCIÓN** según el nombre
- ⚠️ Asegúrate de que el modo producción en la configuración coincida con el tipo de certificado
- ⚠️ Los certificados deben estar en formato PEM válido
- ⚠️ Las rutas se almacenan encriptadas en la base de datos por seguridad

## Archivos Creados/Modificados

- ✅ `storage/app/afip/certificates/wsfe_prod_75e43124c3be273f.crt` (copiado)
- ✅ `storage/app/afip/certificates/clave_privada_paola.key` (copiado)
- ✅ `app/Console/Commands/UpdateAfipCertificates.php` (nuevo comando)
- ✅ `update_afip_certificates.php` (script alternativo)

## Limpieza (Opcional)

Después de completar la actualización, puedes eliminar el script temporal:

```bash
rm update_afip_certificates.php
```

El comando Artisan se mantiene para futuras actualizaciones.

