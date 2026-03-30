import { getCategories, getProducts, formatPrice } from '@/lib/api'
import { notFound } from 'next/navigation'
import Link from 'next/link'
import { ChevronRight } from 'lucide-react'
import ProductCard from '@/components/products/ProductCard'

interface PageProps {
  params: Promise<{ slug: string }>
}

export default async function CategoryPage({ params }: PageProps) {
  const { slug } = await params

  const categories = await getCategories().catch(() => [])
  const category = categories.find((c) => c.slug === slug || String(c.id) === slug)

  if (!category) notFound()

  const productsData = await getProducts({ category_id: category.id }).catch(() => null)
  const products = productsData?.data ?? []

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
            <ChevronRight size={12} />
            <Link href="/productos" style={{ color: '#888' }}>Productos</Link>
            <ChevronRight size={12} />
            <span style={{ color: 'var(--color-primary)' }}>{category.name}</span>
          </nav>
          <h1
            style={{
              fontFamily: 'var(--font-display)',
              fontSize: 'clamp(2rem, 5vw, 3rem)',
              color: 'var(--color-white)',
              fontStyle: 'italic',
            }}
          >
            {category.name}
          </h1>
          {category.description && (
            <p className="mt-2 text-sm" style={{ color: 'var(--color-primary-light)', fontStyle: 'italic' }}>
              {category.description}
            </p>
          )}
        </div>
      </div>

      {/* Products */}
      <section style={{ background: 'var(--color-bg)', padding: '48px 0 80px' }}>
        <div className="max-w-7xl mx-auto px-6">
          <div className="flex items-center justify-between mb-8">
            <p className="text-sm" style={{ color: 'var(--color-dark-soft)' }}>
              <span className="font-semibold" style={{ color: 'var(--color-dark)' }}>{products.length}</span> productos en esta categoría
            </p>
            <Link
              href="/productos"
              className="text-xs font-semibold uppercase tracking-wider pb-0.5"
              style={{ color: 'var(--color-dark)', borderBottom: '1px solid var(--color-primary)' }}
            >
              Ver todos
            </Link>
          </div>

          {products.length === 0 ? (
            <div className="text-center py-20">
              <p
                style={{ fontFamily: 'var(--font-display)', fontSize: '1.5rem', color: 'var(--color-dark)' }}
                className="mb-3"
              >
                Sin productos en esta categoría
              </p>
              <Link href="/productos" className="btn-primary mt-4 inline-block">
                Ver catálogo completo
              </Link>
            </div>
          ) : (
            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
              {products.map((product) => (
                <ProductCard key={product.id} product={product} />
              ))}
            </div>
          )}
        </div>
      </section>
    </>
  )
}
