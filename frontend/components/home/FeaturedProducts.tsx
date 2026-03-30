import Link from 'next/link'
import { ArrowRight } from 'lucide-react'
import { getProducts } from '@/lib/api'
import { Product } from '@/lib/types'
import ProductCard from '@/components/products/ProductCard'

export default async function FeaturedProducts() {
  let products: Product[] = []
  try {
    const data = await getProducts({ featured: true })
    products = data.data.slice(0, 8)
  } catch {
    // Muestra placeholder si no hay API disponible
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

        {/* Grid */}
        {products.length > 0 ? (
          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
            {products.map((product) => (
              <ProductCard key={product.id} product={product} />
            ))}
          </div>
        ) : (
          /* Placeholder cards cuando no hay API */
          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
            {Array.from({ length: 8 }).map((_, i) => (
              <PlaceholderCard key={i} index={i} />
            ))}
          </div>
        )}

        <div className="text-center mt-10 md:hidden">
          <Link href="/productos" className="btn-primary">
            Ver todos los productos
          </Link>
        </div>
      </div>
    </section>
  )
}

function PlaceholderCard({ index }: { index: number }) {
  const names = [
    'Shampoo Hidratante Premium',
    'Máscara Reparadora Intensiva',
    'Acondicionador Nutritivo',
    'Sérum Brillo Extremo',
    'Crema de Peinado Control',
    'Protector Térmico Argan',
    'Ampolla Capilar Restauradora',
    'Gel Fijador Ultra Fuerte',
  ]
  const categories = ['Shampoo', 'Máscaras', 'Acondicionador', 'Tratamientos', 'Styling', 'Protección', 'Ampollas', 'Styling']
  const prices = ['$2.800', '$4.200', '$3.100', '$5.500', '$2.400', '$3.800', '$6.200', '$1.900']

  return (
    <div
      style={{
        background: 'var(--color-white)',
        border: '1px solid var(--color-border)',
      }}
    >
      <div
        className="flex items-center justify-center"
        style={{
          aspectRatio: '1/1',
          background: 'linear-gradient(135deg, var(--color-cream) 0%, var(--color-primary-light) 100%)',
        }}
      >
        <div className="text-center px-4">
          <div
            className="w-16 h-16 rounded-full mx-auto mb-2"
            style={{ background: 'var(--color-primary)', opacity: 0.3 }}
          />
          <span
            className="text-xs uppercase tracking-wider"
            style={{ color: 'var(--color-primary-dark)', fontWeight: 600 }}
          >
            Tiziano
          </span>
        </div>
      </div>
      <div className="p-4">
        <div
          className="text-xs px-2 py-0.5 inline-block mb-2 font-semibold uppercase tracking-wide"
          style={{ background: 'var(--color-cream)', color: 'var(--color-dark-soft)' }}
        >
          {categories[index]}
        </div>
        <h3
          className="font-semibold text-sm mb-3 leading-snug"
          style={{ color: 'var(--color-dark)' }}
        >
          {names[index]}
        </h3>
        <div className="flex items-center justify-between">
          <span
            className="font-bold"
            style={{ color: 'var(--color-dark)', fontFamily: 'var(--font-display)' }}
          >
            {prices[index]}
          </span>
          <button
            className="text-xs font-semibold uppercase tracking-wider px-3 py-2"
            style={{ background: 'var(--color-dark)', color: 'var(--color-white)' }}
          >
            Agregar
          </button>
        </div>
      </div>
    </div>
  )
}
