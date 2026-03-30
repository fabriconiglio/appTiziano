import { getProducts, getCategories, getBrands } from '@/lib/api'
import ProductsClient from './ProductsClient'
import { PaginatedResponse, Product } from '@/lib/types'

const emptyPaginated: PaginatedResponse<Product> = {
  data: [],
  current_page: 1,
  last_page: 1,
  per_page: 24,
  total: 0,
  from: 0,
  to: 0,
  links: [],
}

export default async function ProductosPage() {
  const [initialData, categories, brands] = await Promise.all([
    getProducts().catch(() => emptyPaginated),
    getCategories().catch(() => []),
    getBrands().catch(() => []),
  ])

  return (
    <>
      {/* Page header */}
      <div
        style={{
          background: 'var(--color-dark)',
          padding: '48px 0 40px',
          borderBottom: '3px solid var(--color-primary)',
        }}
      >
        <div className="max-w-7xl mx-auto px-6">
          <nav className="mb-4 flex items-center gap-2 text-xs" style={{ color: '#888' }}>
            <a href="/" style={{ color: '#888' }}>Inicio</a>
            <span>/</span>
            <span style={{ color: 'var(--color-primary)' }}>Productos</span>
          </nav>
          <h1
            style={{
              fontFamily: 'var(--font-display)',
              fontSize: 'clamp(2rem, 5vw, 3rem)',
              color: 'var(--color-white)',
              fontStyle: 'italic',
            }}
          >
            Catálogo de Productos
          </h1>
          <p
            className="mt-2 text-sm"
            style={{ color: 'var(--color-primary-light)', fontStyle: 'italic' }}
          >
            Productos profesionales para el cuidado capilar
          </p>
        </div>
      </div>

      {/* Products + filters */}
      <div style={{ background: 'var(--color-bg)' }}>
        <ProductsClient
          initialData={initialData}
          categories={categories}
          brands={brands}
        />
      </div>
    </>
  )
}
