'use client'

import Link from 'next/link'
import { useRouter } from 'next/navigation'
import { Trash2, Minus, Plus, ShoppingBag, ArrowLeft } from 'lucide-react'
import { useCart } from '@/lib/CartContext'
import { useAuth } from '@/lib/AuthContext'
import { formatPrice } from '@/lib/api'

export default function CarritoPage() {
  const { items, cartTotal, removeItem, updateQuantity, clearCart } = useCart()
  const { isAuthenticated } = useAuth()
  const router = useRouter()

  const handleCheckout = () => {
    if (!isAuthenticated) {
      router.push('/ingresar?redirect=/checkout')
      return
    }
    router.push('/checkout')
  }

  if (items.length === 0) {
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
              <span style={{ color: 'var(--color-primary)' }}>Carrito</span>
            </nav>
            <h1
              style={{
                fontFamily: 'var(--font-display)',
                fontSize: 'clamp(2rem, 5vw, 3rem)',
                color: 'var(--color-white)',
                fontStyle: 'italic',
              }}
            >
              Tu Carrito
            </h1>
          </div>
        </div>

        <div style={{ background: 'var(--color-bg)' }} className="py-20">
          <div className="max-w-7xl mx-auto px-6 text-center">
            <ShoppingBag size={64} style={{ color: 'var(--color-border)' }} className="mx-auto mb-6" />
            <h2 className="text-xl font-semibold mb-2" style={{ color: 'var(--color-dark)' }}>
              Tu carrito está vacío
            </h2>
            <p className="text-sm mb-8" style={{ color: 'var(--color-dark-soft)' }}>
              Explorá nuestro catálogo y encontrá los mejores productos profesionales.
            </p>
            <Link
              href="/productos"
              className="inline-flex items-center gap-2 px-8 py-4 text-sm font-bold uppercase tracking-widest"
              style={{ background: 'var(--color-dark)', color: 'var(--color-white)' }}
            >
              <ArrowLeft size={16} />
              Ver productos
            </Link>
          </div>
        </div>
      </>
    )
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
            <span style={{ color: 'var(--color-primary)' }}>Carrito</span>
          </nav>
          <h1
            style={{
              fontFamily: 'var(--font-display)',
              fontSize: 'clamp(2rem, 5vw, 3rem)',
              color: 'var(--color-white)',
              fontStyle: 'italic',
            }}
          >
            Tu Carrito
          </h1>
        </div>
      </div>

      <div style={{ background: 'var(--color-bg)' }} className="py-12">
        <div className="max-w-7xl mx-auto px-6">
          <div className="grid lg:grid-cols-3 gap-10">
            {/* Items */}
            <div className="lg:col-span-2 flex flex-col gap-4">
              {items.map((item) => {
                const price = typeof item.product.price === 'string' ? parseFloat(item.product.price) : item.product.price
                return (
                  <div
                    key={item.product.id}
                    className="flex gap-4 p-4"
                    style={{
                      background: 'var(--color-white)',
                      border: '1px solid var(--color-border)',
                    }}
                  >
                    {/* Image */}
                    <Link href={`/productos/${item.product.id}`} className="flex-shrink-0">
                      {item.product.image_url ? (
                        <img
                          src={item.product.image_url}
                          alt={item.product.name}
                          className="rounded object-contain"
                          style={{ width: 100, height: 100 }}
                        />
                      ) : (
                        <div
                          className="rounded flex items-center justify-center"
                          style={{ width: 100, height: 100, background: 'var(--color-bg)' }}
                        >
                          <ShoppingBag size={32} style={{ color: 'var(--color-dark-soft)' }} />
                        </div>
                      )}
                    </Link>

                    {/* Info */}
                    <div className="flex-1 min-w-0 flex flex-col">
                      <Link href={`/productos/${item.product.id}`}>
                        <h3 className="font-semibold text-sm mb-1 hover:opacity-70" style={{ color: 'var(--color-dark)' }}>
                          {item.product.name}
                        </h3>
                      </Link>
                      {item.product.brand && (
                        <p className="text-xs mb-2" style={{ color: 'var(--color-dark-soft)', fontStyle: 'italic' }}>
                          {item.product.brand.name}
                        </p>
                      )}
                      <p className="text-sm font-semibold mb-3" style={{ color: 'var(--color-dark)' }}>
                        {formatPrice(item.product.price)}
                      </p>

                      <div className="mt-auto flex items-center justify-between">
                        {/* Quantity */}
                        <div className="flex items-center" style={{ border: '1px solid var(--color-border)' }}>
                          <button
                            onClick={() => updateQuantity(item.product.id, item.quantity - 1)}
                            className="px-2.5 py-1.5 hover:opacity-60"
                            style={{ color: 'var(--color-dark)' }}
                          >
                            <Minus size={14} />
                          </button>
                          <span
                            className="px-3 py-1.5 text-sm font-semibold min-w-[40px] text-center"
                            style={{ color: 'var(--color-dark)', borderLeft: '1px solid var(--color-border)', borderRight: '1px solid var(--color-border)' }}
                          >
                            {item.quantity}
                          </span>
                          <button
                            onClick={() => updateQuantity(item.product.id, item.quantity + 1)}
                            className="px-2.5 py-1.5 hover:opacity-60"
                            style={{ color: 'var(--color-dark)' }}
                          >
                            <Plus size={14} />
                          </button>
                        </div>

                        <div className="flex items-center gap-4">
                          <span className="font-bold text-base" style={{ color: 'var(--color-dark)', fontFamily: 'var(--font-display)' }}>
                            {formatPrice(price * item.quantity)}
                          </span>
                          <button
                            onClick={() => removeItem(item.product.id)}
                            className="p-1.5 hover:opacity-60"
                            style={{ color: '#C25B56' }}
                          >
                            <Trash2 size={16} />
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                )
              })}

              <div className="flex items-center justify-between mt-2">
                <Link
                  href="/productos"
                  className="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider hover:opacity-70"
                  style={{ color: 'var(--color-dark)' }}
                >
                  <ArrowLeft size={14} />
                  Seguir comprando
                </Link>
                <button
                  onClick={clearCart}
                  className="text-xs font-semibold uppercase tracking-wider hover:opacity-70"
                  style={{ color: '#C25B56' }}
                >
                  Vaciar carrito
                </button>
              </div>
            </div>

            {/* Summary */}
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
                  Resumen del pedido
                </h2>

                <div className="flex flex-col gap-3 mb-6 pb-6" style={{ borderBottom: '1px solid var(--color-border)' }}>
                  {items.map((item) => {
                    const price = typeof item.product.price === 'string' ? parseFloat(item.product.price) : item.product.price
                    return (
                      <div key={item.product.id} className="flex justify-between text-sm">
                        <span style={{ color: 'var(--color-dark-soft)' }}>
                          {item.product.name} x{item.quantity}
                        </span>
                        <span style={{ color: 'var(--color-dark)' }} className="font-medium">
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
                  onClick={handleCheckout}
                  className="w-full py-4 text-sm font-bold uppercase tracking-widest transition-all hover:opacity-90"
                  style={{ background: 'var(--color-dark)', color: 'var(--color-white)' }}
                >
                  Proceder al checkout
                </button>

                {!isAuthenticated && (
                  <p className="text-xs text-center mt-3" style={{ color: 'var(--color-dark-soft)' }}>
                    Necesitás estar registrado para comprar
                  </p>
                )}
              </div>
            </div>
          </div>
        </div>
      </div>
    </>
  )
}
