import type { ReactNode } from 'react'
import Link from 'next/link'
import { ArrowRight } from 'lucide-react'
import { getProducts } from '@/lib/api'
import { Product } from '@/lib/types'
import ProductCard from '@/components/products/ProductCard'

export default async function FeaturedProducts() {
  let products: Product[] = []
  let status: 'ok' | 'empty' | 'error' = 'ok'

  try {
    const data = await getProducts({ featured: true })
    products = (data.data ?? []).slice(0, 8)
    if (products.length === 0) status = 'empty'
  } catch {
    status = 'error'
  }

  let featuredBody: ReactNode
  if (status === 'ok' && products.length > 0) {
    featuredBody = (
      <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
        {products.map((product) => (
          <ProductCard key={product.id} product={product} />
        ))}
      </div>
    )
  } else if (status === 'error') {
    featuredBody = (
      <div
        className="rounded-lg p-8 text-center text-sm leading-relaxed"
        style={{ background: 'var(--color-cream)', color: 'var(--color-dark-soft)', border: '1px solid var(--color-border)' }}
      >
        <p className="font-semibold mb-2" style={{ color: 'var(--color-dark)' }}>
          No se pudieron cargar los productos destacados
        </p>
        <p className="mb-0 max-w-2xl mx-auto">
          Comprobá que la API de Laravel esté en marcha y que en el archivo{' '}
          <code className="text-xs">frontend/.env.local</code> la variable{' '}
          <code className="text-xs">NEXT_PUBLIC_API_URL</code> apunte al mismo origen que usa el panel (por
          ejemplo <code className="text-xs">http://localhost:8000</code> en desarrollo).
        </p>
      </div>
    )
  } else {
    featuredBody = (
      <div
        className="rounded-lg p-8 text-center text-sm leading-relaxed"
        style={{ background: 'var(--color-cream)', color: 'var(--color-dark-soft)', border: '1px solid var(--color-border)' }}
      >
        <p className="font-semibold mb-2" style={{ color: 'var(--color-dark)' }}>
          Todavía no hay destacados para mostrar
        </p>
        <p className="mb-0 max-w-2xl mx-auto">
          Activá «Producto destacado en el E-Commerce» en <strong>Distribuidora → Inventario Proveedor</strong>{' '}
          (al editar el ítem o con el atajo de la estrella en el listado). Los destacados pueden tener stock
          cero y siguen apareciendo aquí.
        </p>
      </div>
    )
  }

  return (
    <section style={{ padding: '80px 0', background: 'var(--color-white)' }}>
      <div className="max-w-7xl mx-auto px-6">
        {/* Header */}
        <div className="flex items-end justify-between mb-12">
          <div>
            <p
              className="text-xs uppercase tracking-widest font-semibold mb-3"
              style={{ color: 'var(--color-primary)' }}
            >
              Selección especial
            </p>
            <h2
              style={{
                fontFamily: 'var(--font-display)',
                fontSize: 'clamp(1.75rem, 4vw, 2.5rem)',
                color: 'var(--color-dark)',
                lineHeight: 1.1,
              }}
            >
              Productos Destacados
            </h2>
          </div>
          <Link
            href="/productos"
            className="hidden md:flex items-center gap-2 text-sm font-semibold uppercase tracking-wider pb-1 transition-all"
            style={{
              color: 'var(--color-dark)',
              borderBottom: '1px solid var(--color-primary)',
            }}
          >
            Ver catálogo completo
            <ArrowRight size={16} />
          </Link>
        </div>

        {featuredBody}

        <div className="text-center mt-10 md:hidden">
          <Link href="/productos" className="btn-primary">
            Ver todos los productos
          </Link>
        </div>
      </div>
    </section>
  )
}
