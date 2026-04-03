'use client'

import { useState } from 'react'
import { useRouter } from 'next/navigation'
import Link from 'next/link'
import { CreditCard, Building2, ShoppingBag, Loader2 } from 'lucide-react'
import { useCart } from '@/lib/CartContext'
import { useAuth } from '@/lib/AuthContext'
import { createOrder, formatPrice } from '@/lib/api'

type PaymentMethod = 'taca_taca' | 'transfer'

export default function CheckoutPage() {
  const { items, cartTotal, clearCart } = useCart()
  const { isAuthenticated, token, loading: authLoading } = useAuth()
  const router = useRouter()

  const [paymentMethod, setPaymentMethod] = useState<PaymentMethod>('transfer')
  const [notes, setNotes] = useState('')
  const [submitting, setSubmitting] = useState(false)
  const [error, setError] = useState('')

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

  if (items.length === 0) {
    router.push('/carrito')
    return null
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
          unit_price: typeof item.product.price === 'string' ? parseFloat(item.product.price) : item.product.price,
        })),
        notes: notes || undefined,
      }

      const result = await createOrder(orderData, token)

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

  return (
    <>
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

      <div style={{ background: 'var(--color-bg)' }} className="py-12">
        <div className="max-w-7xl mx-auto px-6">
          <div className="grid lg:grid-cols-3 gap-10">
            {/* Payment method */}
            <div className="lg:col-span-2">
              <h2
                className="text-lg font-semibold mb-6"
                style={{ color: 'var(--color-dark)', fontFamily: 'var(--font-display)', fontStyle: 'italic' }}
              >
                Método de pago
              </h2>

              <div className="flex flex-col gap-4 mb-8">
                {/* Transfer */}
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
                    className="flex items-center justify-center rounded-full flex-shrink-0"
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

                {/* Taca Taca */}
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
                    className="flex items-center justify-center rounded-full flex-shrink-0"
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

              {/* Notes */}
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
            </div>

            {/* Order summary */}
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

                <div className="flex flex-col gap-3 mb-6 pb-6" style={{ borderBottom: '1px solid var(--color-border)' }}>
                  {items.map((item) => {
                    const price = typeof item.product.price === 'string' ? parseFloat(item.product.price) : item.product.price
                    return (
                      <div key={item.product.id} className="flex items-center gap-3">
                        {item.product.image_url ? (
                          <img
                            src={item.product.image_url}
                            alt={item.product.name}
                            className="rounded object-contain flex-shrink-0"
                            style={{ width: 40, height: 40 }}
                          />
                        ) : (
                          <div
                            className="flex-shrink-0 rounded flex items-center justify-center"
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
              </div>
            </div>
          </div>
        </div>
      </div>
    </>
  )
}
