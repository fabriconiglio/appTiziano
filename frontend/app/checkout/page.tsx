'use client'

import { useRef, useState } from 'react'
import { useRouter } from 'next/navigation'
import Link from 'next/link'
import {
  CreditCard,
  Building2,
  ShoppingBag,
  Loader2,
  MapPin,
  Truck,
  Package,
  ChevronRight,
  ChevronLeft,
  Check,
} from 'lucide-react'
import { useCart } from '@/lib/CartContext'
import { useAuth } from '@/lib/AuthContext'
import { createOrder, formatPrice } from '@/lib/api'
import type { ShippingData, ShippingMethod } from '@/lib/types'

type PaymentMethod = 'taca_taca' | 'transfer'

const PROVINCES = [
  'Buenos Aires', 'CABA', 'Catamarca', 'Chaco', 'Chubut', 'Córdoba',
  'Corrientes', 'Entre Ríos', 'Formosa', 'Jujuy', 'La Pampa', 'La Rioja',
  'Mendoza', 'Misiones', 'Neuquén', 'Río Negro', 'Salta', 'San Juan',
  'San Luis', 'Santa Cruz', 'Santa Fe', 'Santiago del Estero',
  'Tierra del Fuego', 'Tucumán',
]

const SHIPPING_OPTIONS: {
  value: ShippingMethod
  label: string
  description: string
  cost: string
  time: string
  Icon: typeof MapPin
}[] = [
  {
    value: 'local_pickup',
    label: 'Retiro en local',
    description: 'Retirá tu pedido en Santa Ana 2725, Loc. 2, Córdoba',
    cost: 'Gratis',
    time: 'Inmediato (una vez confirmado el pago)',
    Icon: MapPin,
  },
  {
    value: 'cordoba',
    label: 'Envío a Córdoba Capital',
    description: 'Entrega por cadete en Córdoba Capital y alrededores',
    cost: 'A coordinar',
    time: '24 a 48 hs hábiles',
    Icon: Truck,
  },
  {
    value: 'national',
    label: 'Envío al interior del país',
    description: 'Por Correo Argentino, Andreani u otro operador',
    cost: 'A coordinar',
    time: '3 a 5 días hábiles',
    Icon: Package,
  },
]

const STEP_LABELS = ['Datos de envío', 'Método de envío', 'Pago']

export default function CheckoutPage() {
  const { items, cartTotal, clearCart } = useCart()
  const { isAuthenticated, user, token, loading: authLoading } = useAuth()
  const router = useRouter()

  const [step, setStep] = useState(1)

  const [shippingData, setShippingData] = useState<ShippingData>({
    shipping_name: '',
    shipping_phone: '',
    shipping_province: 'Córdoba',
    shipping_city: '',
    shipping_zip: '',
    shipping_address: '',
    shipping_address_2: '',
  })
  const [shippingMethod, setShippingMethod] = useState<ShippingMethod>('local_pickup')
  const [paymentMethod, setPaymentMethod] = useState<PaymentMethod>('transfer')
  const [notes, setNotes] = useState('')

  const [submitting, setSubmitting] = useState(false)
  const [error, setError] = useState('')
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({})
  const orderCompletedRef = useRef(false)

  // Pre-fill name from user on first render
  const didPrefill = useRef(false)
  if (user && !didPrefill.current) {
    didPrefill.current = true
    if (!shippingData.shipping_name) {
      setShippingData((prev) => ({ ...prev, shipping_name: user.name }))
    }
  }

  if (authLoading) {
    return (
      <div className="flex items-center justify-center py-32">
        <Loader2 size={32} className="animate-spin" style={{ color: 'var(--color-primary)' }} />
      </div>
    )
  }

  if (!isAuthenticated) {
    router.push('/ingresar?redirect=/checkout')
    return null
  }

  if (items.length === 0 && !orderCompletedRef.current) {
    router.push('/carrito')
    return null
  }

  const updateField = (field: keyof ShippingData, value: string) => {
    setShippingData((prev) => ({ ...prev, [field]: value }))
    if (fieldErrors[field]) {
      setFieldErrors((prev) => {
        const copy = { ...prev }
        delete copy[field]
        return copy
      })
    }
  }

  const validateStep1 = (): boolean => {
    const errors: Record<string, string> = {}
    if (!shippingData.shipping_name.trim()) errors.shipping_name = 'Ingresá tu nombre completo'
    if (!shippingData.shipping_phone.trim()) errors.shipping_phone = 'Ingresá tu teléfono'
    if (!shippingData.shipping_province) errors.shipping_province = 'Seleccioná una provincia'
    if (!shippingData.shipping_city.trim()) errors.shipping_city = 'Ingresá tu ciudad'
    if (!shippingData.shipping_zip.trim()) errors.shipping_zip = 'Ingresá el código postal'
    if (!shippingData.shipping_address.trim()) errors.shipping_address = 'Ingresá tu dirección'
    setFieldErrors(errors)
    return Object.keys(errors).length === 0
  }

  const goToStep = (target: number) => {
    if (target > step) {
      if (step === 1 && !validateStep1()) return
    }
    setError('')
    setStep(target)
  }

  const handleSubmit = async () => {
    if (!token) return
    setSubmitting(true)
    setError('')

    try {
      const orderData = {
        payment_method: paymentMethod,
        items: items.map((item) => ({
          product_id: item.product.id,
          quantity: item.quantity,
          unit_price: typeof item.product.price === 'string' ? Number.parseFloat(item.product.price) : item.product.price,
        })),
        notes: notes || undefined,
        ...shippingData,
        shipping_method: shippingMethod,
      }

      const result = await createOrder(orderData, token)

      orderCompletedRef.current = true
      clearCart()

      if (result.checkout_url) {
        globalThis.location.href = result.checkout_url
      } else {
        router.push(`/checkout/confirmacion?order=${result.order.id}`)
      }
    } catch (err: unknown) {
      const apiErr = err as { message?: string }
      setError(apiErr?.message || 'Error al procesar el pedido. Intentá de nuevo.')
      setSubmitting(false)
    }
  }

  const shippingMethodLabel =
    SHIPPING_OPTIONS.find((o) => o.value === shippingMethod)?.label ?? ''

  const inputStyle = (field?: string) => ({
    border: field && fieldErrors[field] ? '1px solid #EF4444' : '1px solid var(--color-border)',
    background: 'var(--color-white)',
    color: 'var(--color-dark)',
  })

  return (
    <>
      {/* Header */}
      <div
        style={{
          background: 'var(--color-dark)',
          padding: '48px 0 40px',
          borderBottom: '3px solid var(--color-primary)',
        }}
      >
        <div className="max-w-7xl mx-auto px-6">
          <nav className="mb-4 flex items-center gap-2 text-xs" style={{ color: '#888' }}>
            <Link href="/" style={{ color: '#888' }}>Inicio</Link>
            <span>/</span>
            <Link href="/carrito" style={{ color: '#888' }}>Carrito</Link>
            <span>/</span>
            <span style={{ color: 'var(--color-primary)' }}>Checkout</span>
          </nav>
          <h1
            style={{
              fontFamily: 'var(--font-display)',
              fontSize: 'clamp(2rem, 5vw, 3rem)',
              color: 'var(--color-white)',
              fontStyle: 'italic',
            }}
          >
            Finalizar Compra
          </h1>
        </div>
      </div>

      {/* Progress bar */}
      <div style={{ background: 'var(--color-white)', borderBottom: '1px solid var(--color-border)' }}>
        <div className="max-w-7xl mx-auto px-6 py-5">
          <div className="flex items-center justify-between max-w-lg mx-auto">
            {STEP_LABELS.map((label, idx) => {
              const num = idx + 1
              const isActive = num === step
              const isCompleted = num < step
              return (
                <div key={label} className="flex items-center gap-2 flex-1">
                  <button
                    onClick={() => num < step && goToStep(num)}
                    className="flex items-center gap-2"
                    style={{ cursor: num < step ? 'pointer' : 'default' }}
                  >
                    <div
                      className="flex items-center justify-center rounded-full text-xs font-bold shrink-0"
                      style={{
                        width: 28,
                        height: 28,
                        background: isActive
                          ? 'var(--color-dark)'
                          : isCompleted
                            ? 'var(--color-primary)'
                            : 'var(--color-bg)',
                        color: isActive
                          ? 'var(--color-white)'
                          : isCompleted
                            ? 'var(--color-dark)'
                            : 'var(--color-dark-soft)',
                        border: !isActive && !isCompleted ? '1px solid var(--color-border)' : 'none',
                      }}
                    >
                      {isCompleted ? <Check size={14} /> : num}
                    </div>
                    <span
                      className="text-xs font-semibold uppercase tracking-wider hidden sm:inline"
                      style={{ color: isActive ? 'var(--color-dark)' : 'var(--color-dark-soft)' }}
                    >
                      {label}
                    </span>
                  </button>
                  {idx < STEP_LABELS.length - 1 && (
                    <div
                      className="flex-1 mx-2"
                      style={{
                        height: 2,
                        background: isCompleted ? 'var(--color-primary)' : 'var(--color-border)',
                      }}
                    />
                  )}
                </div>
              )
            })}
          </div>
        </div>
      </div>

      <div style={{ background: 'var(--color-bg)' }} className="py-12">
        <div className="max-w-7xl mx-auto px-6">
          <div className="grid lg:grid-cols-3 gap-10">
            {/* Left: step content */}
            <div className="lg:col-span-2">
              {/* STEP 1: Shipping data */}
              {step === 1 && (
                <div>
                  <h2
                    className="text-lg font-semibold mb-6"
                    style={{ color: 'var(--color-dark)', fontFamily: 'var(--font-display)', fontStyle: 'italic' }}
                  >
                    Datos de envío
                  </h2>

                  <div
                    className="p-6"
                    style={{ background: 'var(--color-white)', border: '1px solid var(--color-border)' }}
                  >
                    <div className="grid sm:grid-cols-2 gap-4">
                      {/* Name */}
                      <div className="sm:col-span-2">
                        <label htmlFor="shipping_name" className="block text-xs font-semibold uppercase tracking-wider mb-1.5" style={{ color: 'var(--color-dark-soft)' }}>
                          Nombre completo *
                        </label>
                        <input
                          id="shipping_name"
                          type="text"
                          value={shippingData.shipping_name}
                          onChange={(e) => updateField('shipping_name', e.target.value)}
                          className="w-full px-4 py-3 text-sm outline-none"
                          style={inputStyle('shipping_name')}
                          placeholder="Juan Pérez"
                        />
                        {fieldErrors.shipping_name && (
                          <p className="text-xs mt-1" style={{ color: '#EF4444' }}>{fieldErrors.shipping_name}</p>
                        )}
                      </div>

                      {/* Phone */}
                      <div>
                        <label htmlFor="shipping_phone" className="block text-xs font-semibold uppercase tracking-wider mb-1.5" style={{ color: 'var(--color-dark-soft)' }}>
                          Teléfono *
                        </label>
                        <input
                          id="shipping_phone"
                          type="tel"
                          value={shippingData.shipping_phone}
                          onChange={(e) => updateField('shipping_phone', e.target.value)}
                          className="w-full px-4 py-3 text-sm outline-none"
                          style={inputStyle('shipping_phone')}
                          placeholder="351 1234567"
                        />
                        {fieldErrors.shipping_phone && (
                          <p className="text-xs mt-1" style={{ color: '#EF4444' }}>{fieldErrors.shipping_phone}</p>
                        )}
                      </div>

                      {/* Province */}
                      <div>
                        <label htmlFor="shipping_province" className="block text-xs font-semibold uppercase tracking-wider mb-1.5" style={{ color: 'var(--color-dark-soft)' }}>
                          Provincia *
                        </label>
                        <select
                          id="shipping_province"
                          value={shippingData.shipping_province}
                          onChange={(e) => updateField('shipping_province', e.target.value)}
                          className="w-full px-4 py-3 text-sm outline-none"
                          style={inputStyle('shipping_province')}
                        >
                          <option value="">Seleccionar...</option>
                          {PROVINCES.map((p) => (
                            <option key={p} value={p}>{p}</option>
                          ))}
                        </select>
                        {fieldErrors.shipping_province && (
                          <p className="text-xs mt-1" style={{ color: '#EF4444' }}>{fieldErrors.shipping_province}</p>
                        )}
                      </div>

                      {/* City */}
                      <div>
                        <label htmlFor="shipping_city" className="block text-xs font-semibold uppercase tracking-wider mb-1.5" style={{ color: 'var(--color-dark-soft)' }}>
                          Ciudad *
                        </label>
                        <input
                          id="shipping_city"
                          type="text"
                          value={shippingData.shipping_city}
                          onChange={(e) => updateField('shipping_city', e.target.value)}
                          className="w-full px-4 py-3 text-sm outline-none"
                          style={inputStyle('shipping_city')}
                          placeholder="Córdoba"
                        />
                        {fieldErrors.shipping_city && (
                          <p className="text-xs mt-1" style={{ color: '#EF4444' }}>{fieldErrors.shipping_city}</p>
                        )}
                      </div>

                      {/* ZIP */}
                      <div>
                        <label htmlFor="shipping_zip" className="block text-xs font-semibold uppercase tracking-wider mb-1.5" style={{ color: 'var(--color-dark-soft)' }}>
                          Código postal *
                        </label>
                        <input
                          id="shipping_zip"
                          type="text"
                          value={shippingData.shipping_zip}
                          onChange={(e) => updateField('shipping_zip', e.target.value)}
                          className="w-full px-4 py-3 text-sm outline-none"
                          style={inputStyle('shipping_zip')}
                          placeholder="5000"
                        />
                        {fieldErrors.shipping_zip && (
                          <p className="text-xs mt-1" style={{ color: '#EF4444' }}>{fieldErrors.shipping_zip}</p>
                        )}
                      </div>

                      {/* Address */}
                      <div className="sm:col-span-2">
                        <label htmlFor="shipping_address" className="block text-xs font-semibold uppercase tracking-wider mb-1.5" style={{ color: 'var(--color-dark-soft)' }}>
                          Dirección (calle y número) *
                        </label>
                        <input
                          id="shipping_address"
                          type="text"
                          value={shippingData.shipping_address}
                          onChange={(e) => updateField('shipping_address', e.target.value)}
                          className="w-full px-4 py-3 text-sm outline-none"
                          style={inputStyle('shipping_address')}
                          placeholder="Av. Colón 1234"
                        />
                        {fieldErrors.shipping_address && (
                          <p className="text-xs mt-1" style={{ color: '#EF4444' }}>{fieldErrors.shipping_address}</p>
                        )}
                      </div>

                      {/* Address 2 */}
                      <div className="sm:col-span-2">
                        <label className="block text-xs font-semibold uppercase tracking-wider mb-1.5" style={{ color: 'var(--color-dark-soft)' }}>
                          Piso / Departamento (opcional)
                        </label>
                        <input
                          type="text"
                          value={shippingData.shipping_address_2 ?? ''}
                          onChange={(e) => updateField('shipping_address_2', e.target.value)}
                          className="w-full px-4 py-3 text-sm outline-none"
                          style={inputStyle()}
                          placeholder="Piso 3, Depto B"
                        />
                      </div>
                    </div>

                    {/* Next button */}
                    <div className="mt-6 flex justify-end">
                      <button
                        onClick={() => goToStep(2)}
                        className="flex items-center gap-2 px-8 py-3 text-sm font-bold uppercase tracking-widest transition-all hover:opacity-90"
                        style={{ background: 'var(--color-dark)', color: 'var(--color-white)' }}
                      >
                        Continuar
                        <ChevronRight size={16} />
                      </button>
                    </div>
                  </div>
                </div>
              )}

              {/* STEP 2: Shipping method */}
              {step === 2 && (
                <div>
                  <h2
                    className="text-lg font-semibold mb-6"
                    style={{ color: 'var(--color-dark)', fontFamily: 'var(--font-display)', fontStyle: 'italic' }}
                  >
                    Método de envío
                  </h2>

                  <div className="flex flex-col gap-4 mb-8">
                    {SHIPPING_OPTIONS.map((opt) => (
                      <button
                        key={opt.value}
                        onClick={() => setShippingMethod(opt.value)}
                        className="flex items-start gap-4 p-5 text-left transition-all"
                        style={{
                          background: 'var(--color-white)',
                          border: shippingMethod === opt.value
                            ? '2px solid var(--color-primary)'
                            : '1px solid var(--color-border)',
                        }}
                      >
                        <div
                          className="flex items-center justify-center rounded-full shrink-0"
                          style={{
                            width: 48,
                            height: 48,
                            background: shippingMethod === opt.value ? 'var(--color-primary)' : 'var(--color-bg)',
                          }}
                        >
                          <opt.Icon
                            size={22}
                            style={{
                              color: shippingMethod === opt.value ? 'var(--color-dark)' : 'var(--color-dark-soft)',
                            }}
                          />
                        </div>
                        <div className="flex-1">
                          <p className="font-semibold text-sm mb-1" style={{ color: 'var(--color-dark)' }}>
                            {opt.label}
                          </p>
                          <p className="text-xs mb-2" style={{ color: 'var(--color-dark-soft)' }}>
                            {opt.description}
                          </p>
                          <div className="flex flex-wrap gap-4 text-xs">
                            <span style={{ color: 'var(--color-dark)' }}>
                              <strong>Costo:</strong> {opt.cost}
                            </span>
                            <span style={{ color: 'var(--color-dark)' }}>
                              <strong>Plazo:</strong> {opt.time}
                            </span>
                          </div>
                        </div>
                      </button>
                    ))}
                  </div>

                  <div className="flex justify-between">
                    <button
                      onClick={() => goToStep(1)}
                      className="flex items-center gap-2 px-6 py-3 text-sm font-bold uppercase tracking-widest transition-all hover:opacity-80"
                      style={{ border: '1px solid var(--color-dark)', color: 'var(--color-dark)' }}
                    >
                      <ChevronLeft size={16} />
                      Volver
                    </button>
                    <button
                      onClick={() => goToStep(3)}
                      className="flex items-center gap-2 px-8 py-3 text-sm font-bold uppercase tracking-widest transition-all hover:opacity-90"
                      style={{ background: 'var(--color-dark)', color: 'var(--color-white)' }}
                    >
                      Continuar
                      <ChevronRight size={16} />
                    </button>
                  </div>
                </div>
              )}

              {/* STEP 3: Payment */}
              {step === 3 && (
                <div>
                  <h2
                    className="text-lg font-semibold mb-6"
                    style={{ color: 'var(--color-dark)', fontFamily: 'var(--font-display)', fontStyle: 'italic' }}
                  >
                    Método de pago
                  </h2>

                  <div className="flex flex-col gap-4 mb-8">
                    <button
                      onClick={() => setPaymentMethod('transfer')}
                      className="flex items-start gap-4 p-5 text-left transition-all"
                      style={{
                        background: 'var(--color-white)',
                        border: paymentMethod === 'transfer'
                          ? '2px solid var(--color-primary)'
                          : '1px solid var(--color-border)',
                      }}
                    >
                      <div
                        className="flex items-center justify-center rounded-full shrink-0"
                        style={{
                          width: 48,
                          height: 48,
                          background: paymentMethod === 'transfer' ? 'var(--color-primary)' : 'var(--color-bg)',
                        }}
                      >
                        <Building2
                          size={22}
                          style={{
                            color: paymentMethod === 'transfer' ? 'var(--color-dark)' : 'var(--color-dark-soft)',
                          }}
                        />
                      </div>
                      <div>
                        <p className="font-semibold text-sm mb-1" style={{ color: 'var(--color-dark)' }}>
                          Transferencia bancaria
                        </p>
                        <p className="text-xs" style={{ color: 'var(--color-dark-soft)' }}>
                          Realizá una transferencia y envianos el comprobante. Confirmamos tu pedido en minutos.
                        </p>
                      </div>
                    </button>

                    <button
                      onClick={() => setPaymentMethod('taca_taca')}
                      className="flex items-start gap-4 p-5 text-left transition-all"
                      style={{
                        background: 'var(--color-white)',
                        border: paymentMethod === 'taca_taca'
                          ? '2px solid var(--color-primary)'
                          : '1px solid var(--color-border)',
                      }}
                    >
                      <div
                        className="flex items-center justify-center rounded-full shrink-0"
                        style={{
                          width: 48,
                          height: 48,
                          background: paymentMethod === 'taca_taca' ? 'var(--color-primary)' : 'var(--color-bg)',
                        }}
                      >
                        <CreditCard
                          size={22}
                          style={{
                            color: paymentMethod === 'taca_taca' ? 'var(--color-dark)' : 'var(--color-dark-soft)',
                          }}
                        />
                      </div>
                      <div>
                        <p className="font-semibold text-sm mb-1" style={{ color: 'var(--color-dark)' }}>
                          Taca Taca (Tarjeta de crédito/débito)
                        </p>
                        <p className="text-xs" style={{ color: 'var(--color-dark-soft)' }}>
                          Pagá con tarjeta de crédito o débito de forma segura a través de Taca Taca.
                        </p>
                      </div>
                    </button>
                  </div>

                  <h2
                    className="text-lg font-semibold mb-4"
                    style={{ color: 'var(--color-dark)', fontFamily: 'var(--font-display)', fontStyle: 'italic' }}
                  >
                    Notas del pedido
                  </h2>
                  <textarea
                    value={notes}
                    onChange={(e) => setNotes(e.target.value)}
                    placeholder="Alguna indicación especial para tu pedido (opcional)"
                    maxLength={500}
                    rows={3}
                    className="w-full px-4 py-3 text-sm outline-none resize-none mb-8"
                    style={{
                      border: '1px solid var(--color-border)',
                      background: 'var(--color-white)',
                      color: 'var(--color-dark)',
                    }}
                  />

                  {error && (
                    <div
                      className="p-4 mb-6 text-sm"
                      style={{ background: '#FEE2E2', color: '#991B1B', border: '1px solid #FECACA' }}
                    >
                      {error}
                    </div>
                  )}

                  <div className="flex justify-between">
                    <button
                      onClick={() => goToStep(2)}
                      className="flex items-center gap-2 px-6 py-3 text-sm font-bold uppercase tracking-widest transition-all hover:opacity-80"
                      style={{ border: '1px solid var(--color-dark)', color: 'var(--color-dark)' }}
                    >
                      <ChevronLeft size={16} />
                      Volver
                    </button>
                  </div>
                </div>
              )}
            </div>

            {/* Right: order summary sidebar */}
            <div>
              <div
                className="p-6 sticky top-28"
                style={{
                  background: 'var(--color-white)',
                  border: '1px solid var(--color-border)',
                }}
              >
                <h2
                  className="text-lg font-semibold mb-6"
                  style={{ color: 'var(--color-dark)', fontFamily: 'var(--font-display)', fontStyle: 'italic' }}
                >
                  Tu pedido
                </h2>

                {/* Items */}
                <div className="flex flex-col gap-3 mb-5 pb-5" style={{ borderBottom: '1px solid var(--color-border)' }}>
                  {items.map((item) => {
                    const price = typeof item.product.price === 'string' ? Number.parseFloat(item.product.price) : item.product.price
                    return (
                      <div key={item.product.id} className="flex items-center gap-3">
                        {item.product.image_url ? (
                          <img
                            src={item.product.image_url}
                            alt={item.product.name}
                            className="rounded object-contain shrink-0"
                            style={{ width: 40, height: 40 }}
                          />
                        ) : (
                          <div
                            className="shrink-0 rounded flex items-center justify-center"
                            style={{ width: 40, height: 40, background: 'var(--color-bg)' }}
                          >
                            <ShoppingBag size={16} style={{ color: 'var(--color-dark-soft)' }} />
                          </div>
                        )}
                        <div className="flex-1 min-w-0">
                          <p className="text-xs truncate" style={{ color: 'var(--color-dark)' }}>
                            {item.product.name}
                          </p>
                          <p className="text-xs" style={{ color: 'var(--color-dark-soft)' }}>
                            x{item.quantity}
                          </p>
                        </div>
                        <span className="text-sm font-medium" style={{ color: 'var(--color-dark)' }}>
                          {formatPrice(price * item.quantity)}
                        </span>
                      </div>
                    )
                  })}
                </div>

                {/* Shipping info summary */}
                {step >= 2 && shippingData.shipping_name && (
                  <div className="mb-5 pb-5" style={{ borderBottom: '1px solid var(--color-border)' }}>
                    <p className="text-xs uppercase tracking-wider font-semibold mb-2" style={{ color: 'var(--color-dark-soft)' }}>
                      Enviar a
                    </p>
                    <p className="text-sm" style={{ color: 'var(--color-dark)' }}>
                      {shippingData.shipping_name}
                    </p>
                    <p className="text-xs" style={{ color: 'var(--color-dark-soft)' }}>
                      {shippingData.shipping_address}
                      {shippingData.shipping_address_2 ? `, ${shippingData.shipping_address_2}` : ''}
                    </p>
                    <p className="text-xs" style={{ color: 'var(--color-dark-soft)' }}>
                      {shippingData.shipping_city}, {shippingData.shipping_province} ({shippingData.shipping_zip})
                    </p>
                    <p className="text-xs" style={{ color: 'var(--color-dark-soft)' }}>
                      Tel: {shippingData.shipping_phone}
                    </p>
                  </div>
                )}

                {/* Shipping method summary */}
                {step >= 3 && (
                  <div className="mb-5 pb-5" style={{ borderBottom: '1px solid var(--color-border)' }}>
                    <p className="text-xs uppercase tracking-wider font-semibold mb-2" style={{ color: 'var(--color-dark-soft)' }}>
                      Método de envío
                    </p>
                    <p className="text-sm" style={{ color: 'var(--color-dark)' }}>
                      {shippingMethodLabel}
                    </p>
                  </div>
                )}

                {/* Total */}
                <div className="flex justify-between items-center mb-6">
                  <span className="text-sm font-semibold uppercase tracking-wider" style={{ color: 'var(--color-dark)' }}>
                    Total
                  </span>
                  <span
                    className="text-2xl font-bold"
                    style={{ color: 'var(--color-dark)', fontFamily: 'var(--font-display)' }}
                  >
                    {formatPrice(cartTotal)}
                  </span>
                </div>

                {/* Submit button (only on step 3) */}
                {step === 3 && (
                  <button
                    onClick={handleSubmit}
                    disabled={submitting}
                    className="w-full py-4 text-sm font-bold uppercase tracking-widest transition-all hover:opacity-90 flex items-center justify-center gap-2"
                    style={{
                      background: submitting ? '#999' : 'var(--color-dark)',
                      color: 'var(--color-white)',
                      cursor: submitting ? 'not-allowed' : 'pointer',
                    }}
                  >
                    {submitting && <Loader2 size={16} className="animate-spin" />}
                    {submitting ? 'Procesando...' : 'Confirmar pedido'}
                  </button>
                )}
              </div>
            </div>
          </div>
        </div>
      </div>
    </>
  )
}
