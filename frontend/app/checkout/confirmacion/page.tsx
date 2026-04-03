'use client'

import { useEffect, useState, Suspense } from 'react'
import { useSearchParams } from 'next/navigation'
import Link from 'next/link'
import { CheckCircle, Copy, Check, Loader2, MessageCircle, Mail } from 'lucide-react'
import { useAuth } from '@/lib/AuthContext'
import { getOrder, formatPrice } from '@/lib/api'
import { Order } from '@/lib/types'

const BANK_INFO = {
  banco: 'Banco Nación Argentina',
  titular: 'Tiziano Peluquería & Spa',
  cbu: '0110012345678901234567',
  alias: 'TIZIANO.PELUQUERIA',
}

function ConfirmacionContent() {
  const searchParams = useSearchParams()
  const { token, loading: authLoading } = useAuth()
  const [order, setOrder] = useState<Order | null>(null)
  const [loading, setLoading] = useState(true)
  const [copiedField, setCopiedField] = useState<string | null>(null)

  const orderId = searchParams.get('order')

  useEffect(() => {
    if (authLoading || !token || !orderId) return
    getOrder(parseInt(orderId), token)
      .then(setOrder)
      .catch(() => {})
      .finally(() => setLoading(false))
  }, [orderId, token, authLoading])

  const copyToClipboard = (text: string, field: string) => {
    navigator.clipboard.writeText(text)
    setCopiedField(field)
    setTimeout(() => setCopiedField(null), 2000)
  }

  if (loading || authLoading) {
    return (
      <div className="flex items-center justify-center py-32">
        <Loader2 size={32} className="animate-spin" style={{ color: 'var(--color-primary)' }} />
      </div>
    )
  }

  if (!order) {
    return (
      <div className="py-20 text-center">
        <p className="text-sm" style={{ color: 'var(--color-dark-soft)' }}>No se encontró el pedido.</p>
        <Link href="/productos" className="text-sm font-semibold mt-4 inline-block" style={{ color: 'var(--color-primary)' }}>
          Volver a productos
        </Link>
      </div>
    )
  }

  const isTransfer = order.payment_method === 'transfer'

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
            <span style={{ color: 'var(--color-primary)' }}>Confirmación</span>
          </nav>
          <h1
            style={{
              fontFamily: 'var(--font-display)',
              fontSize: 'clamp(2rem, 5vw, 3rem)',
              color: 'var(--color-white)',
              fontStyle: 'italic',
            }}
          >
            Pedido Confirmado
          </h1>
        </div>
      </div>

      <div style={{ background: 'var(--color-bg)' }} className="py-12">
        <div className="max-w-2xl mx-auto px-6">
          {/* Success banner */}
          <div
            className="flex items-center gap-4 p-6 mb-8"
            style={{
              background: '#F0FDF4',
              border: '1px solid #BBF7D0',
            }}
          >
            <CheckCircle size={40} style={{ color: '#2E7D52' }} className="flex-shrink-0" />
            <div>
              <p className="font-semibold text-base" style={{ color: '#2E7D52' }}>
                ¡Pedido registrado con éxito!
              </p>
              <p className="text-sm" style={{ color: '#166534' }}>
                Número de pedido: <strong>{order.order_number}</strong>
              </p>
            </div>
          </div>

          {/* Order details */}
          <div
            className="p-6 mb-6"
            style={{ background: 'var(--color-white)', border: '1px solid var(--color-border)' }}
          >
            <h2
              className="text-lg font-semibold mb-4"
              style={{ color: 'var(--color-dark)', fontFamily: 'var(--font-display)', fontStyle: 'italic' }}
            >
              Detalle del pedido
            </h2>

            <div className="flex flex-col gap-3 mb-4 pb-4" style={{ borderBottom: '1px solid var(--color-border)' }}>
              {order.items.map((item) => (
                <div key={item.id} className="flex justify-between text-sm">
                  <span style={{ color: 'var(--color-dark-soft)' }}>
                    {item.product_name} x{item.quantity}
                  </span>
                  <span className="font-medium" style={{ color: 'var(--color-dark)' }}>
                    {formatPrice(item.subtotal)}
                  </span>
                </div>
              ))}
            </div>

            <div className="flex justify-between items-center">
              <span className="font-semibold text-sm uppercase tracking-wider" style={{ color: 'var(--color-dark)' }}>
                Total
              </span>
              <span className="text-xl font-bold" style={{ color: 'var(--color-dark)', fontFamily: 'var(--font-display)' }}>
                {formatPrice(order.total)}
              </span>
            </div>
          </div>

          {/* Shipping info */}
          {order.shipping_name && (
            <div
              className="p-6 mb-6"
              style={{ background: 'var(--color-white)', border: '1px solid var(--color-border)' }}
            >
              <h2
                className="text-lg font-semibold mb-4"
                style={{ color: 'var(--color-dark)', fontFamily: 'var(--font-display)', fontStyle: 'italic' }}
              >
                Datos de envío
              </h2>
              <div className="grid sm:grid-cols-2 gap-3">
                <div>
                  <p className="text-xs uppercase tracking-wider font-semibold" style={{ color: 'var(--color-dark-soft)' }}>
                    Destinatario
                  </p>
                  <p className="text-sm mt-0.5" style={{ color: 'var(--color-dark)' }}>{order.shipping_name}</p>
                </div>
                <div>
                  <p className="text-xs uppercase tracking-wider font-semibold" style={{ color: 'var(--color-dark-soft)' }}>
                    Teléfono
                  </p>
                  <p className="text-sm mt-0.5" style={{ color: 'var(--color-dark)' }}>{order.shipping_phone}</p>
                </div>
                <div>
                  <p className="text-xs uppercase tracking-wider font-semibold" style={{ color: 'var(--color-dark-soft)' }}>
                    Dirección
                  </p>
                  <p className="text-sm mt-0.5" style={{ color: 'var(--color-dark)' }}>
                    {order.shipping_address}
                    {order.shipping_address_2 ? `, ${order.shipping_address_2}` : ''}
                  </p>
                </div>
                <div>
                  <p className="text-xs uppercase tracking-wider font-semibold" style={{ color: 'var(--color-dark-soft)' }}>
                    Localidad
                  </p>
                  <p className="text-sm mt-0.5" style={{ color: 'var(--color-dark)' }}>
                    {order.shipping_city}, {order.shipping_province} ({order.shipping_zip})
                  </p>
                </div>
                <div className="sm:col-span-2">
                  <p className="text-xs uppercase tracking-wider font-semibold" style={{ color: 'var(--color-dark-soft)' }}>
                    Método de envío
                  </p>
                  <p className="text-sm mt-0.5" style={{ color: 'var(--color-dark)' }}>
                    {order.shipping_method === 'local_pickup' && 'Retiro en local'}
                    {order.shipping_method === 'cordoba' && 'Envío a Córdoba Capital'}
                    {order.shipping_method === 'national' && 'Envío al interior del país'}
                  </p>
                </div>
              </div>
            </div>
          )}

          {/* Transfer info */}
          {isTransfer && (
            <div
              className="p-6 mb-6"
              style={{ background: 'var(--color-white)', border: '1px solid var(--color-border)' }}
            >
              <h2
                className="text-lg font-semibold mb-4"
                style={{ color: 'var(--color-dark)', fontFamily: 'var(--font-display)', fontStyle: 'italic' }}
              >
                Datos para la transferencia
              </h2>

              <p className="text-sm mb-5" style={{ color: 'var(--color-dark-soft)' }}>
                Realizá la transferencia con los siguientes datos y envianos el comprobante por WhatsApp o email.
              </p>

              <div className="flex flex-col gap-4">
                {[
                  { label: 'Banco', value: BANK_INFO.banco, key: 'banco' },
                  { label: 'Titular', value: BANK_INFO.titular, key: 'titular' },
                  { label: 'CBU', value: BANK_INFO.cbu, key: 'cbu' },
                  { label: 'Alias', value: BANK_INFO.alias, key: 'alias' },
                  { label: 'Monto', value: formatPrice(order.total), key: 'monto' },
                  { label: 'Referencia', value: order.order_number, key: 'ref' },
                ].map((row) => (
                  <div
                    key={row.key}
                    className="flex items-center justify-between p-3"
                    style={{ background: 'var(--color-bg)', border: '1px solid var(--color-border)' }}
                  >
                    <div>
                      <p className="text-xs uppercase tracking-wider font-semibold" style={{ color: 'var(--color-dark-soft)' }}>
                        {row.label}
                      </p>
                      <p className="text-sm font-medium mt-0.5" style={{ color: 'var(--color-dark)' }}>
                        {row.value}
                      </p>
                    </div>
                    <button
                      onClick={() => copyToClipboard(row.value, row.key)}
                      className="p-2 hover:opacity-60"
                      style={{ color: 'var(--color-dark-soft)' }}
                      title="Copiar"
                    >
                      {copiedField === row.key ? (
                        <Check size={16} style={{ color: '#2E7D52' }} />
                      ) : (
                        <Copy size={16} />
                      )}
                    </button>
                  </div>
                ))}
              </div>

              <div
                className="mt-5 p-4 text-sm"
                style={{
                  background: '#FEF9C3',
                  border: '1px solid #FDE68A',
                  color: '#854D0E',
                }}
              >
                <strong>Importante:</strong> Una vez realizada la transferencia, envianos el comprobante
                por WhatsApp o email indicando el número de pedido. Confirmaremos tu pedido en minutos.
              </div>

              <div className="mt-4 flex flex-col sm:flex-row gap-3">
                <a
                  href={`https://wa.me/5493516197836?text=${encodeURIComponent(
                    `Hola! Realicé una transferencia para el pedido #${order.order_number} por ${formatPrice(order.total)}. Adjunto el comprobante.`
                  )}`}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="flex-1 flex items-center justify-center gap-2 py-3 px-4 text-sm font-bold uppercase tracking-wider text-white"
                  style={{ background: '#25D366' }}
                >
                  <MessageCircle size={18} />
                  Enviar por WhatsApp
                </a>
                <a
                  href={`mailto:tizianopeluqueriaspa@gmail.com?subject=${encodeURIComponent(
                    `Comprobante de transferencia - Pedido #${order.order_number}`
                  )}&body=${encodeURIComponent(
                    `Hola,\n\nRealicé una transferencia para el pedido #${order.order_number} por ${formatPrice(order.total)}.\n\nAdjunto el comprobante de pago.\n\nSaludos.`
                  )}`}
                  className="flex-1 flex items-center justify-center gap-2 py-3 px-4 text-sm font-bold uppercase tracking-wider"
                  style={{ border: '1px solid var(--color-dark)', color: 'var(--color-dark)' }}
                >
                  <Mail size={18} />
                  Enviar por Email
                </a>
              </div>
            </div>
          )}

          {/* Taca Taca info */}
          {!isTransfer && (
            <div
              className="p-6 mb-6"
              style={{ background: 'var(--color-white)', border: '1px solid var(--color-border)' }}
            >
              <h2
                className="text-lg font-semibold mb-4"
                style={{ color: 'var(--color-dark)', fontFamily: 'var(--font-display)', fontStyle: 'italic' }}
              >
                Estado del pago
              </h2>
              <p className="text-sm" style={{ color: 'var(--color-dark-soft)' }}>
                {order.payment_status === 'paid'
                  ? 'El pago fue procesado con éxito. ¡Gracias por tu compra!'
                  : order.payment_status === 'failed'
                    ? 'Hubo un problema con el pago. Contactanos para resolverlo.'
                    : 'Tu pago está siendo procesado. Te notificaremos cuando se confirme.'}
              </p>
            </div>
          )}

          {/* Actions */}
          <div className="flex flex-col sm:flex-row gap-3">
            <Link
              href="/mi-cuenta"
              className="flex-1 text-center py-4 text-sm font-bold uppercase tracking-widest"
              style={{ background: 'var(--color-dark)', color: 'var(--color-white)' }}
            >
              Mis pedidos
            </Link>
            <Link
              href="/productos"
              className="flex-1 text-center py-4 text-sm font-bold uppercase tracking-widest"
              style={{ border: '1px solid var(--color-dark)', color: 'var(--color-dark)' }}
            >
              Seguir comprando
            </Link>
          </div>
        </div>
      </div>
    </>
  )
}

export default function ConfirmacionPage() {
  return (
    <Suspense fallback={
      <div className="flex items-center justify-center py-32">
        <Loader2 size={32} className="animate-spin" style={{ color: 'var(--color-primary)' }} />
      </div>
    }>
      <ConfirmacionContent />
    </Suspense>
  )
}
