# CLAUDE.md — appTiziano

Reglas y contexto para trabajar en este repo. Si trabajás dentro de `frontend/`, además se carga [frontend/CLAUDE.md](frontend/CLAUDE.md) + [frontend/AGENTS.md](frontend/AGENTS.md).

---

## 1. Idioma

- **Respondé siempre en español** (rioplatense está bien).
- Código, nombres de tabla, campos, rutas, vistas y comentarios están **en español**. No renombrar a inglés, no "normalizar". Si agregás código nuevo, seguí el mismo idioma (ej: `clientes_no_frecuentes`, `cuenta_corriente`, `ventas_diarias`).
- Dinero y fechas: `es-AR`, moneda **ARS**.

## 2. Arquitectura (monorepo)

- **Raíz** = app Laravel 11 (PHP 8.2) — panel interno (Blade) + API pública (Sanctum).
- **`frontend/`** = Next.js 16.2 / React 19 / Tailwind 4 — ecommerce público que consume la API.
- Frontend se comunica con backend vía `NEXT_PUBLIC_API_URL` (por defecto `http://localhost:8000`), token Bearer en `localStorage` (`tiziano_auth_token`).

## 3. Dominio — 3 negocios en paralelo

No mezclar módulos. Cada uno tiene sus controllers/vistas/migraciones:

1. **Peluquería** — ventas diarias, servicios, técnicas, clientes, cuenta corriente de clientes. Prefijos: `hairdressing_*`, `technical_records`, `daily_sales`.
2. **Distribuidora** — clientes distribuidores, presupuestos, descuentos, cuentas corrientes de proveedores y clientes. Prefijos: `distributor_*`, `supplier_*`.
3. **Ecommerce** — catálogo público, órdenes, usuarios frontend (Next.js). Modelos: `Product`, `Category`, `Brand`, `Order`, `Slider`.

Transversales: **AFIP** (factura A/B/C), stock + alertas, Google OAuth login.

## 4. Backend — stack y convenciones

- Laravel 11 · PHP 8.2 · Sanctum · Blade + Vite + Tailwind + Bootstrap 5.
- Controllers **planos** en [app/Http/Controllers](app/Http/Controllers) (sin subcarpetas, salvo [app/Http/Controllers/Api](app/Http/Controllers/Api) para REST).
- Servicios en [app/Services](app/Services) **solamente** para integraciones externas (hoy: `AfipService`, `MercadoPagoService`, `AndreaniService`). No inflar con capas de servicio internas.
- Middleware [SetLocale.php](app/Http/Middleware/SetLocale.php) fuerza locale `es` — no lo toques.
- Rutas:
  - [routes/web.php](routes/web.php) — panel interno, protegido por `auth` + `restrict.inventory`.
  - [routes/api.php](routes/api.php) — API pública del ecommerce, con `auth:sanctum`.
- Libs en uso (no reemplazar sin motivo): `barryvdh/laravel-dompdf` (PDFs), `phpoffice/phpspreadsheet` (Excel), `simplesoftwareio/simple-qrcode`, `afipsdk/afip.php`, `google/apiclient`, `alejoasotelo/andreani` (cotización y envíos Andreani).
- **Tests**: sólo scaffold. No inventes una suite; si agregás tests, que sean feature tests reales contra SQLite de prueba.
- **Migraciones**: 91 existentes. Nueva migración sólo si la pide el cambio. Nunca borrar/modificar migraciones ya ejecutadas en prod.

## 5. Frontend — stack y convenciones

- **Next.js 16.2** (App Router) — **breaking changes vs. versiones anteriores**. Antes de escribir código de framework, leé `node_modules/next/dist/docs/` (ver advertencia en [frontend/AGENTS.md](frontend/AGENTS.md)).
- React 19, TypeScript 5, Tailwind 4 (`@tailwindcss/postcss`), íconos `lucide-react`.
- **Sin shadcn/ui, sin Radix, sin axios.** Usá `fetch` nativo.
- Estado: React Context (`AuthContext`, `CartContext` en [frontend/lib](frontend/lib)) + `localStorage` (`tiziano_auth_token`, `tiziano_auth_user`, `tiziano_cart`). **No meter Redux/Zustand** salvo que lo pida el usuario.
- Cliente API centralizado en [frontend/lib/api.ts](frontend/lib/api.ts). Tipos compartidos en [frontend/lib/types.ts](frontend/lib/types.ts).
- Rutas clave: `/productos[/id]`, `/categorias[/slug]`, `/carrito`, `/checkout[/confirmacion]`, `/mi-cuenta`, `/ingresar`, `/registro`.

## 6. Integraciones sensibles — pedir confirmación antes de tocar

- **AFIP / facturación** — leer [FACTURACION_AFIP_README.md](FACTURACION_AFIP_README.md) y [docs/ACTUALIZACION_CERTIFICADOS_AFIP.md](docs/ACTUALIZACION_CERTIFICADOS_AFIP.md) antes. Nunca modificar `config/afip.php`, certificados ni `AfipService` sin pedir ok.
- **Pasarela de pago** — **Mercado Pago** (Checkout Pro, SDK `mercadopago/dx-php`). Credenciales en `.env` (`MERCADOPAGO_ACCESS_TOKEN`, `MERCADOPAGO_PUBLIC_KEY`, `MERCADOPAGO_WEBHOOK_SECRET`). Webhook: `POST /api/mercadopago/webhook` (público) protegido por el middleware `mp.signature` ([VerifyMercadoPagoSignature.php](app/Http/Middleware/VerifyMercadoPagoSignature.php)) que valida HMAC-SHA256 del header `x-signature` contra el `MERCADOPAGO_WEBHOOK_SECRET`. Si el secret no está seteado, el middleware deja pasar (modo dev). El enum `payment_method` solo acepta `mercadopago` y `transfer`.
- **Andreani (envíos)** — SDK `alejoasotelo/andreani`, servicio en [app/Services/AndreaniService.php](app/Services/AndreaniService.php). Credenciales en `.env` (`ANDREANI_USER`, `ANDREANI_PASSWORD`, `ANDREANI_CLIENTE`, `ANDREANI_CONTRATO`, `ANDREANI_CP_ORIGEN`). Endpoint público: `POST /api/shipping/quote`. La cotización requiere que los productos tengan `peso_gramos` y `volumen_cm3` cargados en `supplier_inventories`. Córdoba Capital: Uber Motos (sin API, solo texto). Nacional: Andreani en tiempo real.
- **Google OAuth** — credenciales en `.env`, endpoint `POST /api/auth/google`.
- **`.env`** — contiene credenciales sensibles (Mercado Pago, AFIP, Google, Andreani). **Nunca commitear.**

## 7. Reglas de trabajo

- **No ejecutar sin confirmación explícita**:
  - Seeders, `db:wipe`, `db:seed`.
  - Scripts `deploy_*.sh`, `setup_*_cron.sh` y demás de la raíz.
  - Comandos AFIP (`php artisan afip:*`).
  - `composer update`, `npm update`, upgrades de Laravel/Next.
- Preferir **editar** controllers/vistas existentes antes de crear nuevos.
- No inventar abstracciones: tres líneas parecidas son mejores que un helper prematuro.
- Respuestas breves; no summaries al final de cada edición.
- Si el cambio afecta crons o cuentas corrientes, avisar riesgos antes.

## 8. Archivos críticos — referencia rápida

| Área | Path |
|---|---|
| Rutas internas | [routes/web.php](routes/web.php) |
| API pública | [routes/api.php](routes/api.php) |
| Config servicios externos | [config/services.php](config/services.php) |
| AFIP | [app/Services/AfipService.php](app/Services/AfipService.php) · [config/afip.php](config/afip.php) |
| Pasarela | [app/Services/MercadoPagoService.php](app/Services/MercadoPagoService.php) · [app/Http/Middleware/VerifyMercadoPagoSignature.php](app/Http/Middleware/VerifyMercadoPagoSignature.php) · [app/Http/Controllers/Api/OrderApiController.php](app/Http/Controllers/Api/OrderApiController.php) |
| Andreani (envíos) | [app/Services/AndreaniService.php](app/Services/AndreaniService.php) · [app/Http/Controllers/Api/ShippingApiController.php](app/Http/Controllers/Api/ShippingApiController.php) |
| Checkout FE | [frontend/app/checkout/page.tsx](frontend/app/checkout/page.tsx) · [frontend/app/checkout/confirmacion/page.tsx](frontend/app/checkout/confirmacion/page.tsx) |
| Cliente API FE | [frontend/lib/api.ts](frontend/lib/api.ts) · [frontend/lib/types.ts](frontend/lib/types.ts) |
| Stock jobs/alertas | [app/Jobs/CheckLowStock.php](app/Jobs/CheckLowStock.php) · [app/Console/Commands](app/Console/Commands) |

## 9. Documentación existente — consultar antes de duplicar

- [README.md](README.md) — Laravel base.
- [FACTURACION_AFIP_README.md](FACTURACION_AFIP_README.md) — flujo AFIP, tipos de factura, certificados.
- [CRON_SETUP.md](CRON_SETUP.md) — cron base de stock.
- [DAILY_SALES_SETUP.md](DAILY_SALES_SETUP.md) · [HAIRDRESSING_DAILY_SALES_SETUP.md](HAIRDRESSING_DAILY_SALES_SETUP.md) · [PRODUCTION_READY_DAILY_SALES.md](PRODUCTION_READY_DAILY_SALES.md) — ventas diarias + reset automático.
- [PRODUCTION_STOCK_ALERTS_SETUP.md](PRODUCTION_STOCK_ALERTS_SETUP.md) — alertas de stock en prod.
- [DEPLOY_AUTOMATION.md](DEPLOY_AUTOMATION.md) · [DEPLOY_HAIRDRESSING_AUTOMATION.md](DEPLOY_HAIRDRESSING_AUTOMATION.md) — scripts de deploy.
- [docs/COMPONENTE_FILTROS.md](docs/COMPONENTE_FILTROS.md) — filtros reutilizables en Blade.
- [docs/DIAGNOSTICO_MYSQL_MOVILES.md](docs/DIAGNOSTICO_MYSQL_MOVILES.md) — troubleshooting acceso móvil.
- [docs/ACTUALIZACION_CERTIFICADOS_AFIP.md](docs/ACTUALIZACION_CERTIFICADOS_AFIP.md) — renovación certificados.

## 10. Gotchas conocidos

- **Webhook de MP en dev**: `notification_url` apunta a `APP_URL + /api/mercadopago/webhook`. En `localhost` MP no llega — usar ngrok/cloudflare tunnel o probar directo en staging. Si el webhook no corre, el `payment_status` queda `pending` hasta que se marque a mano desde el admin. En **prod** hay que setear `MERCADOPAGO_WEBHOOK_SECRET`: si la firma `x-signature` no matchea el HMAC esperado, el middleware `mp.signature` devuelve 401 y MP reintenta.
- **Next 16** es pre-release: APIs, convenciones y file structure pueden diferir de lo conocido. Ver aviso en [frontend/AGENTS.md](frontend/AGENTS.md).
- **Controllers planos**: no crear subcarpetas en `app/Http/Controllers/` (sólo `Api/` existe a propósito).
- **SetLocale.php** fuerza `es` en cada request — no cachear respuestas con otro locale.
- **Andreani requiere peso/volumen**: si un producto del carrito no tiene `peso_gramos` y `volumen_cm3` cargados, el endpoint `POST /api/shipping/quote` devuelve `available: false` y el checkout muestra "Contactanos para cotizar". Hay que cargar esos campos desde el admin de inventarios.
- **`.env`** tiene secrets reales de sandbox — nunca pegarlos en chats, commits, ni PRs.
