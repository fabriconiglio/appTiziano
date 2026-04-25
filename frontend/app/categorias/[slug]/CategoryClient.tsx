'use client'

import { useState, useCallback } from 'react'
import { ChevronLeft, ChevronRight } from 'lucide-react'
import { getProducts } from '@/lib/api'
import { Product, Category, PaginatedResponse } from '@/lib/types'
import ProductCard from '@/components/products/ProductCard'

interface CategoryClientProps {
  initialData: PaginatedResponse<Product>
  category: Category
}

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

export default function CategoryClient({ initialData, category }: CategoryClientProps) {
  const [data, setData] = useState(initialData)
  const [page, setPage] = useState(1)
  const [loading, setLoading] = useState(false)

  const fetchPage = useCallback(async (nextPage: number) => {
    setLoading(true)
    try {
      const result = await getProducts({ category_id: category.id, page: nextPage })
      setData(result)
      setPage(nextPage)
      window.scrollTo({ top: 0, behavior: 'smooth' })
    } finally {
      setLoading(false)
    }
  }, [category.id])

  return (
    <section style={{ background: 'var(--color-bg)', padding: '48px 0 80px' }}>
      <div className="max-w-7xl mx-auto px-6">
        <div className="flex items-center justify-between mb-8">
          <p className="text-sm" style={{ color: 'var(--color-dark-soft)' }}>
            <span className="font-semibold" style={{ color: 'var(--color-dark)' }}>
              {data.total}
            </span>{' '}
            productos en esta categoría
          </p>
        </div>

        {loading ? (
          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
            {Array.from({ length: 8 }).map((_, i) => (
              <div
                key={i}
                className="animate-pulse"
                style={{
                  aspectRatio: '3/4',
                  background: 'var(--color-cream)',
                  border: '1px solid var(--color-border)',
                }}
              />
            ))}
          </div>
        ) : data.data.length === 0 ? (
          <div className="text-center py-20">
            <p
              style={{ fontFamily: 'var(--font-display)', fontSize: '1.5rem', color: 'var(--color-dark)' }}
              className="mb-3"
            >
              Sin productos en esta categoría
            </p>
          </div>
        ) : (
          <>
            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
              {data.data.map((product) => (
                <ProductCard key={product.id} product={product} />
              ))}
            </div>

            <p className="mt-4 text-xs text-center" style={{ color: '#999' }}>
              * Los precios publicados no incluyen impuestos. Ley N° 27.743, Art. 39.
            </p>

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
                  <span className="font-semibold" style={{ color: 'var(--color-dark)' }}>
                    {data.total}
                  </span>
                </p>
                <div className="flex flex-wrap items-center justify-center gap-1 sm:gap-2">
                  <button
                    type="button"
                    onClick={() => fetchPage(page - 1)}
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
                          onClick={() => fetchPage(item)}
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
                    onClick={() => fetchPage(page + 1)}
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
    </section>
  )
}
