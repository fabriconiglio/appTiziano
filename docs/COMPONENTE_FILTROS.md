# Componente de Filtros Estandarizado

## Descripción

El componente `<x-filters>` proporciona una interfaz de filtros consistente y reutilizable para todos los módulos de la aplicación. Esto asegura una experiencia de usuario uniforme y facilita el mantenimiento del código.

## Ubicación

```
resources/views/components/filters.blade.php
```

## Parámetros

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `route` | string | Sí | Ruta donde se enviará el formulario |
| `filters` | array | No | Array de configuraciones de filtros adicionales |
| `showSearch` | boolean | No | Mostrar campo de búsqueda (default: true) |
| `searchPlaceholder` | string | No | Placeholder para el campo de búsqueda |

## Tipos de Filtros Soportados

### 1. Select (Dropdown)
```php
[
    'name' => 'status',
    'type' => 'select',
    'placeholder' => 'Todos los estados',
    'options' => [
        'active' => 'Activo',
        'inactive' => 'Inactivo'
    ]
]
```

### 2. Date (Fecha)
```php
[
    'name' => 'date_from',
    'type' => 'date',
    'placeholder' => 'Desde'
]
```

### 3. Text (Texto)
```php
[
    'name' => 'description',
    'type' => 'text',
    'placeholder' => 'Descripción'
]
```

## Ejemplos de Uso

### Filtro Simple (Solo Búsqueda)
```blade
<x-filters 
    :route="route('module.index')" 
    :filters="[]" 
    :showSearch="true"
    searchPlaceholder="Buscar..." />
```

### Filtros Múltiples
```blade
@php
    $filters = [
        [
            'name' => 'status',
            'type' => 'select',
            'placeholder' => 'Todos los estados',
            'options' => [
                'active' => 'Activo',
                'inactive' => 'Inactivo'
            ]
        ],
        [
            'name' => 'date_from',
            'type' => 'date',
            'placeholder' => 'Desde'
        ],
        [
            'name' => 'date_to',
            'type' => 'date',
            'placeholder' => 'Hasta'
        ]
    ];
@endphp

<x-filters 
    :route="route('facturacion.index')" 
    :filters="$filters" 
    :showSearch="true"
    searchPlaceholder="Buscar por número, cliente..." />
```

## Características

### Auto-submit
- Los campos de tipo `select` se envían automáticamente al cambiar
- Los campos de tipo `text` y `date` requieren hacer clic en "Buscar"

### Botón Limpiar
- Aparece automáticamente cuando hay filtros activos
- Limpia todos los filtros y regresa a la vista sin parámetros

### Responsive Design
- Se adapta a diferentes tamaños de pantalla
- Usa clases de Bootstrap para el layout

## Implementación en Controladores

El controlador debe manejar los parámetros enviados por el componente:

```php
public function index(Request $request)
{
    $query = Model::query();

    // Filtro de búsqueda general
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    // Filtros específicos
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('date_from')) {
        $query->where('created_at', '>=', $request->date_from);
    }

    if ($request->filled('date_to')) {
        $query->where('created_at', '<=', $request->date_to);
    }

    $results = $query->paginate(20);
    
    return view('module.index', compact('results'));
}
```

## Migración de Filtros Existentes

Para migrar un módulo existente al nuevo componente:

1. **Reemplazar el HTML del formulario** por el componente `<x-filters>`
2. **Actualizar el controlador** para manejar el parámetro `search`
3. **Eliminar JavaScript** relacionado con auto-submit (ya está incluido)
4. **Probar** la funcionalidad en diferentes escenarios

## Beneficios

- ✅ **Consistencia visual** en toda la aplicación
- ✅ **Mantenimiento centralizado** del código de filtros
- ✅ **Reutilización** en múltiples módulos
- ✅ **Responsive design** automático
- ✅ **Funcionalidad completa** (auto-submit, limpiar, etc.)
- ✅ **Flexibilidad** para diferentes tipos de filtros

## Módulos Actualizados

- ✅ Facturación AFIP
- ✅ Presupuestos - Distribuidores
- 🔄 Pendientes: Clientes, Productos, Proveedores, etc.















