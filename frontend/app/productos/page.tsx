import type { Metadata } from 'next'
import { Suspense } from 'react'
import { redirect } from 'next/navigation'
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

type ProductosPageProps = {
  searchParams: Promise<{ marca?: string; brand_id?: string; search?: string }>
}

export async function generateMetadata({ searchParams }: ProductosPageProps): Promise<Metadata> {
  const p = await searchParams
  const marca = typeof p.marca === 'string' ? p.marca : undefined
  if (!marca) {
    return {
      title: 'Catálogo de productos — Tiziano',
      description:
        'Productos profesionales para peluquería. Shampoos, acondicionadores, coloración y más.',
    }
  }
  const brands = await getBrands().catch(() => [])
  const brand = brands.find((b) => b.slug === marca)
  if (brand) {
    const desc = brand.description
      ? `${brand.description.slice(0, 155)}${brand.description.length > 155 ? '…' : ''}`
      : `Comprá productos ${brand.name} en Tiziano. Envíos y retiro en Córdoba.`
    return {
      title: `${brand.name} — Productos — Tiziano`,
      description: desc,
    }
  }
  return { title: 'Catálogo de productos — Tiziano' }
}

export default async function ProductosPage({ searchParams }: ProductosPageProps) {
  const p = await searchParams
  const marca = typeof p.marca === 'string' ? p.marca : undefined
  const rawBid = typeof p.brand_id === 'string' ? p.brand_id : undefined
  const searchQuery = typeof p.search === 'string' ? p.search : undefined

  const [categories, brands] = await Promise.all([
    getCategories().catch(() => []),
    getBrands().catch(() => []),
  ])

  if (rawBid && !marca) {
    const id = parseInt(rawBid, 10)
    if (!Number.isNaN(id)) {
      const b = brands.find((x) => x.id === id)
      if (b?.slug) {
        const qs = new URLSearchParams()
        qs.set('marca', b.slug)
        if (searchQuery) qs.set('search', searchQuery)
        redirect(`/productos?${qs.toString()}`)
      }
    }
  }

  let brandSlug: string | undefined
  let brandId: number | undefined
  let initialBrandId: number | null = null

  if (marca) {
    const b = brands.find((x) => x.slug === marca)
    if (b) {
      brandSlug = b.slug
      initialBrandId = b.id
    }
  } else if (rawBid) {
    const id = parseInt(rawBid, 10)
    if (!Number.isNaN(id)) {
      brandId = id
      initialBrandId = id
    }
  }

  const initialData = await getProducts({
    brand_slug: brandSlug,
    brand_id: brandSlug ? undefined : brandId,
    search: searchQuery,
    page: 1,
  }).catch(() => emptyPaginated)

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
            <a href="/" style={{ color: '#888' }}>
              Inicio
            </a>
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
        <Suspense>
          <ProductsClient
            initialData={initialData}
            categories={categories}
            brands={brands}
            initialBrandId={initialBrandId}
          />
        </Suspense>
      </div>
    </>
  )
}
