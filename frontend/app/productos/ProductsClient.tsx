'use client'

import { useState, useEffect, useCallback } from 'react'
import { Search, SlidersHorizontal, X, ChevronLeft, ChevronRight } from 'lucide-react'
import { getProducts } from '@/lib/api'
import { Product, Category, Brand, PaginatedResponse } from '@/lib/types'
import ProductCard from '@/components/products/ProductCard'
import FilterSidebar from '@/components/products/FilterSidebar'

interface ProductsClientProps {
  initialData: PaginatedResponse<Product>
  categories: Category[]
  brands: Brand[]
}

/** Páginas a mostrar: [1, '…', 5, 6, 7, '…', 28] */
function paginationItems(current: number, lastPage: number, delta = 2): (number | 'ellipsis')[] {
  if (lastPage <= 1) return [1]

  const pages = new Set<number>()
  pages.add(1)
  pages.add(lastPage)
  for (let i = current - delta; i <= current + delta; i++) {
    if (i >= 1 && i <= lastPage) pages.add(i)
  }

  const sorted = [...pages].sort((a, b) => a - b)
  const out: (number | 'ellipsis')[] = []
  let prev = 0
  for (const p of sorted) {
    if (p - prev > 1) out.push('ellipsis')
    out.push(p)
    prev = p
  }
  return out
}

export default function ProductsClient({ initialData, categories, brands }: ProductsClientProps) {
  const [data, setData] = useState(initialData)
  const [selectedCategory, setSelectedCategory] = useState<number | null>(null)
  const [selectedBrand, setSelectedBrand] = useState<number | null>(null)
  const [search, setSearch] = useState('')
  const [page, setPage] = useState(1)
  const [loading, setLoading] = useState(false)
  const [mobileFilters, setMobileFilters] = useState(false)

  const fetchProducts = useCallback(async () => {
    setLoading(true)
    try {
      const result = await getProducts({
        category_id: selectedCategory ?? undefined,
        brand_id: selectedBrand ?? undefined,
        search: search || undefined,
        page,
      })
      setData(result)
    } finally {
      setLoading(false)
    }
  }, [selectedCategory, selectedBrand, search, page])

  useEffect(() => {
    fetchProducts()
  }, [fetchProducts])

  const handleCategoryChange = (id: number | null) => {
    setSelectedCategory(id)
    setPage(1)
  }

  const handleBrandChange = (id: number | null) => {
    setSelectedBrand(id)
    setPage(1)
  }

  return (
    <div>
      {/* Search bar */}
      <div className="max-w-7xl mx-auto px-6 py-6">
        <div className="flex gap-3 items-center">
          <div className="relative flex-1 max-w-md">
            <Search
              size={16}
              className="absolute left-3.5 top-1/2 -translate-y-1/2"
              style={{ color: 'var(--color-dark-soft)' }}
            />
            <input
              type="text"
              placeholder="Buscar productos..."
              value={search}
              onChange={(e) => {
                setSearch(e.target.value)
                setPage(1)
              }}
              className="w-full pl-10 pr-4 py-2.5 text-sm outline-none"
              style={{
                border: '1px solid var(--color-border)',
                background: 'var(--color-white)',
                color: 'var(--color-dark)',
                fontFamily: 'var(--font-body)',
              }}
            />
          </div>

          <button
            className="lg:hidden flex items-center gap-2 px-4 py-2.5 text-sm font-semibold uppercase tracking-wider"
            style={{ border: '1px solid var(--color-dark)', color: 'var(--color-dark)' }}
            onClick={() => setMobileFilters(true)}
          >
            <SlidersHorizontal size={14} />
            Filtros
          </button>

          <p className="text-sm ml-auto" style={{ color: 'var(--color-dark-soft)' }}>
            <span className="font-semibold" style={{ color: 'var(--color-dark)' }}>{data.total}</span> productos
          </p>
        </div>
      </div>

      {/* Mobile filters overlay */}
      {mobileFilters && (
        <div className="fixed inset-0 z-50 lg:hidden">
          <div className="absolute inset-0 bg-black/50" onClick={() => setMobileFilters(false)} />
          <div
            className="absolute right-0 top-0 bottom-0 w-72 p-6 overflow-y-auto"
            style={{ background: 'var(--color-white)' }}
          >
            <button
              onClick={() => setMobileFilters(false)}
              className="mb-6 flex items-center gap-2 text-sm"
              style={{ color: 'var(--color-dark)' }}
            >
              <X size={16} /> Cerrar
            </button>
            <FilterSidebar
              categories={categories}
              brands={brands}
              selectedCategory={selectedCategory}
              selectedBrand={selectedBrand}
              onCategoryChange={(id) => { handleCategoryChange(id); setMobileFilters(false) }}
              onBrandChange={(id) => { handleBrandChange(id); setMobileFilters(false) }}
            />
          </div>
        </div>
      )}

      {/* Main content */}
      <div className="max-w-7xl mx-auto px-6 pb-16 flex gap-8 items-start">
        {/* Desktop sidebar */}
        <div className="hidden lg:block">
          <FilterSidebar
            categories={categories}
            brands={brands}
            selectedCategory={selectedCategory}
            selectedBrand={selectedBrand}
            onCategoryChange={handleCategoryChange}
            onBrandChange={handleBrandChange}
          />
        </div>

        {/* Products */}
        <div className="flex-1 min-w-0">
          {loading ? (
            <div className="grid grid-cols-2 md:grid-cols-3 gap-5">
              {Array.from({ length: 6 }).map((_, i) => (
                <div
                  key={i}
                  className="animate-pulse"
                  style={{ aspectRatio: '3/4', background: 'var(--color-cream)', border: '1px solid var(--color-border)' }}
                />
              ))}
            </div>
          ) : data.data.length === 0 ? (
            <div className="text-center py-20">
              <p
                style={{ fontFamily: 'var(--font-display)', fontSize: '1.5rem', color: 'var(--color-dark)' }}
                className="mb-3"
              >
                Sin resultados
              </p>
              <p style={{ color: 'var(--color-dark-soft)' }}>Probá con otros filtros o términos de búsqueda.</p>
            </div>
          ) : (
            <>
              <div className="grid grid-cols-2 md:grid-cols-3 gap-5">
                {data.data.map((product) => (
                  <ProductCard key={product.id} product={product} />
                ))}
              </div>

              {/* Pagination */}
              {data.last_page > 1 && (
                <nav
                  className="mt-12 flex flex-col items-center gap-4"
                  aria-label="Paginación de productos"
                >
                  <p className="text-sm" style={{ color: 'var(--color-dark-soft)' }}>
                    Mostrando{' '}
                    <span className="font-semibold" style={{ color: 'var(--color-dark)' }}>
                      {data.from ?? 0}
                    </span>
                    –
                    <span className="font-semibold" style={{ color: 'var(--color-dark)' }}>
                      {data.to ?? 0}
                    </span>{' '}
                    de{' '}
                    <span className="font-semibold" style={{ color: 'var(--color-dark)' }}>{data.total}</span>
                  </p>
                  <div className="flex flex-wrap items-center justify-center gap-1 sm:gap-2">
                    <button
                      type="button"
                      onClick={() => {
                        if (page <= 1) return
                        setPage(page - 1)
                        window.scrollTo({ top: 0, behavior: 'smooth' })
                      }}
                      disabled={page <= 1}
                      className="flex h-10 items-center gap-1 rounded px-2 text-sm font-semibold transition-opacity disabled:pointer-events-none disabled:opacity-35"
                      style={{
                        color: 'var(--color-dark)',
                        border: '1px solid var(--color-border)',
                        background: 'var(--color-white)',
                      }}
                      aria-label="Página anterior"
                    >
                      <ChevronLeft size={18} />
                      <span className="hidden sm:inline">Anterior</span>
                    </button>

                    <div className="flex flex-wrap items-center justify-center gap-1">
                      {paginationItems(page, data.last_page).map((item, idx) =>
                        item === 'ellipsis' ? (
                          <span
                            key={`e-${idx}`}
                            className="flex h-10 min-w-10 items-center justify-center px-1 text-sm font-semibold"
                            style={{ color: 'var(--color-dark-soft)' }}
                            aria-hidden
                          >
                            …
                          </span>
                        ) : (
                          <button
                            key={item}
                            type="button"
                            onClick={() => {
                              setPage(item)
                              window.scrollTo({ top: 0, behavior: 'smooth' })
                            }}
                            className="h-10 min-w-10 rounded px-2 text-sm font-semibold transition-all"
                            style={{
                              background: item === page ? 'var(--color-dark)' : 'transparent',
                              color: item === page ? 'var(--color-white)' : 'var(--color-dark)',
                              border: `1px solid ${item === page ? 'var(--color-dark)' : 'var(--color-border)'}`,
                            }}
                            aria-current={item === page ? 'page' : undefined}
                          >
                            {item}
                          </button>
                        )
                      )}
                    </div>

                    <button
                      type="button"
                      onClick={() => {
                        if (page >= data.last_page) return
                        setPage(page + 1)
                        window.scrollTo({ top: 0, behavior: 'smooth' })
                      }}
                      disabled={page >= data.last_page}
                      className="flex h-10 items-center gap-1 rounded px-2 text-sm font-semibold transition-opacity disabled:pointer-events-none disabled:opacity-35"
                      style={{
                        color: 'var(--color-dark)',
                        border: '1px solid var(--color-border)',
                        background: 'var(--color-white)',
                      }}
                      aria-label="Página siguiente"
                    >
                      <span className="hidden sm:inline">Siguiente</span>
                      <ChevronRight size={18} />
                    </button>
                  </div>
                </nav>
              )}
        </>
          )}
        </div>
      </div>
    </div>
  )
}
