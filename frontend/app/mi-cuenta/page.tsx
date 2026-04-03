'use client'

import { useState, useEffect } from 'react'
import Link from 'next/link'
import { useRouter } from 'next/navigation'
import { ChevronRight, User, LogOut, Package, Loader2 } from 'lucide-react'
import { useAuth } from '@/lib/AuthContext'
import { getOrders, formatPrice } from '@/lib/api'
import { Order } from '@/lib/types'

const STATUS_LABELS: Record<string, string> = {
  pending: 'Pendiente',
  confirmed: 'Confirmado',
  processing: 'En proceso',
  shipped: 'Enviado',
  delivered: 'Entregado',
  cancelled: 'Cancelado',
}

const PAYMENT_LABELS: Record<string, string> = {
  pending: 'Pendiente',
  paid: 'Pagado',
  failed: 'Fallido',
}

const STATUS_COLORS: Record<string, string> = {
  pending: '#D97706',
  confirmed: '#2563EB',
  processing: '#7C3AED',
  shipped: '#6B7280',
  delivered: '#2E7D52',
  cancelled: '#C25B56',
}

export default function MiCuentaPage() {
  const { user, token, loading, isAuthenticated, logout } = useAuth()
  const router = useRouter()
  const [orders, setOrders] = useState<Order[]>([])
  const [ordersLoading, setOrdersLoading] = useState(true)

  useEffect(() => {
    if (!token || loading) return
    getOrders(token)
      .then(setOrders)
      .catch(() => {})
      .finally(() => setOrdersLoading(false))
  }, [token, loading])

  if (loading) {
    return (
      <div style={{ padding: '120px 0', textAlign: 'center', color: 'var(--color-dark-soft)' }}>
        Cargando...
      </div>
    )
  }

  if (!isAuthenticated) {
    router.replace('/ingresar')
    return null
  }

  async function handleLogout() {
    await logout()
    router.push('/')
  }

  return (
    <>
      <div
        style={{
          background: 'var(--color-dark)',
          padding: '56px 0 48px',
          borderBottom: '3px solid var(--color-primary)',
        }}
      >
        <div className="max-w-7xl mx-auto px-6">
          <nav className="mb-4 flex items-center gap-2 text-xs" style={{ color: '#888' }}>
            <Link href="/" style={{ color: '#888' }}>Inicio</Link>
            <ChevronRight size={12} />
            <span style={{ color: 'var(--color-primary)' }}>Mi cuenta</span>
          </nav>
          <h1
            style={{
              fontFamily: 'var(--font-display)',
              fontSize: 'clamp(2rem, 5vw, 3rem)',
              color: 'var(--color-white)',
              fontStyle: 'italic',
            }}
          >
            Mi cuenta
          </h1>
        </div>
      </div>

      <section style={{ background: 'var(--color-bg)', padding: '64px 0' }}>
        <div className="max-w-2xl mx-auto px-6">
          <div className="p-8" style={{ background: 'var(--color-white)', border: '1px solid var(--color-border)' }}>
            <div className="flex items-center gap-4 mb-8">
              <div
                className="w-16 h-16 rounded-full flex items-center justify-center"
                style={{ background: 'var(--color-dark)', color: 'var(--color-white)' }}
              >
                <User size={28} />
              </div>
              <div>
                <h2 className="text-lg font-bold" style={{ color: 'var(--color-dark)' }}>
                  {user?.name}
                </h2>
                <p className="text-sm" style={{ color: 'var(--color-dark-soft)' }}>
                  {user?.email}
                </p>
              </div>
            </div>

            <div className="space-y-4">
              <div className="p-4" style={{ background: 'var(--color-bg)', border: '1px solid var(--color-border)' }}>
                <p className="text-xs font-semibold uppercase tracking-wider mb-1" style={{ color: 'var(--color-dark-soft)' }}>
                  Nombre
                </p>
                <p className="text-sm font-medium" style={{ color: 'var(--color-dark)' }}>
                  {user?.name}
                </p>
              </div>
              <div className="p-4" style={{ background: 'var(--color-bg)', border: '1px solid var(--color-border)' }}>
                <p className="text-xs font-semibold uppercase tracking-wider mb-1" style={{ color: 'var(--color-dark-soft)' }}>
                  Email
                </p>
                <p className="text-sm font-medium" style={{ color: 'var(--color-dark)' }}>
                  {user?.email}
                </p>
              </div>
            </div>

            <button
              onClick={handleLogout}
              className="mt-8 w-full flex items-center justify-center gap-2 px-6 py-3 text-sm font-semibold uppercase tracking-wider transition-opacity hover:opacity-90"
              style={{ background: 'var(--color-dark)', color: 'var(--color-white)' }}
            >
              <LogOut size={16} />
              Cerrar sesión
            </button>
          </div>

          {/* Mis pedidos */}
          <div className="mt-8 p-8" style={{ background: 'var(--color-white)', border: '1px solid var(--color-border)' }}>
            <div className="flex items-center gap-3 mb-6">
              <Package size={22} style={{ color: 'var(--color-primary)' }} />
              <h2
                className="text-lg font-semibold"
                style={{ color: 'var(--color-dark)', fontFamily: 'var(--font-display)', fontStyle: 'italic' }}
              >
                Mis pedidos
              </h2>
            </div>

            {ordersLoading ? (
              <div className="flex items-center justify-center py-8">
                <Loader2 size={24} className="animate-spin" style={{ color: 'var(--color-primary)' }} />
              </div>
            ) : orders.length === 0 ? (
              <div className="text-center py-8">
                <Package size={40} style={{ color: 'var(--color-border)' }} className="mx-auto mb-3" />
                <p className="text-sm mb-4" style={{ color: 'var(--color-dark-soft)' }}>
                  Todavía no tenés pedidos
                </p>
                <Link
                  href="/productos"
                  className="inline-block px-6 py-2.5 text-xs font-semibold uppercase tracking-wider"
                  style={{ background: 'var(--color-dark)', color: 'var(--color-white)' }}
                >
                  Ver productos
                </Link>
              </div>
            ) : (
              <div className="flex flex-col gap-3">
                {orders.map((order) => (
                  <Link
                    key={order.id}
                    href={`/checkout/confirmacion?order=${order.id}`}
                    className="flex items-center justify-between p-4 transition-all hover:opacity-80"
                    style={{ background: 'var(--color-bg)', border: '1px solid var(--color-border)' }}
                  >
                    <div>
                      <p className="text-sm font-semibold" style={{ color: 'var(--color-dark)' }}>
                        {order.order_number}
                      </p>
                      <p className="text-xs mt-0.5" style={{ color: 'var(--color-dark-soft)' }}>
                        {new Date(order.created_at).toLocaleDateString('es-AR', {
                          day: '2-digit',
                          month: 'long',
                          year: 'numeric',
                        })}
                        {' · '}
                        {order.items.length} {order.items.length === 1 ? 'producto' : 'productos'}
                      </p>
                    </div>
                    <div className="text-right flex-shrink-0">
                      <p className="text-sm font-bold" style={{ color: 'var(--color-dark)', fontFamily: 'var(--font-display)' }}>
                        {formatPrice(order.total)}
                      </p>
                      <div className="flex items-center gap-2 mt-1 justify-end">
                        <span
                          className="text-[10px] font-semibold uppercase tracking-wider px-2 py-0.5"
                          style={{
                            color: '#fff',
                            background: STATUS_COLORS[order.status] || '#6B7280',
                          }}
                        >
                          {STATUS_LABELS[order.status] || order.status}
                        </span>
                        <span
                          className="text-[10px] font-semibold uppercase tracking-wider px-2 py-0.5"
                          style={{
                            color: order.payment_status === 'paid' ? '#fff' : 'var(--color-dark)',
                            background: order.payment_status === 'paid' ? '#2E7D52' : order.payment_status === 'failed' ? '#C25B56' : '#FDE68A',
                          }}
                        >
                          {PAYMENT_LABELS[order.payment_status] || order.payment_status}
                        </span>
                      </div>
                    </div>
                  </Link>
                ))}
              </div>
            )}
          </div>
        </div>
      </section>
    </>
  )
}
