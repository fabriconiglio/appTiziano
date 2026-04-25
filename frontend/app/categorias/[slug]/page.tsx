import type { Metadata } from 'next'
import { Suspense } from 'react'
import { getCategories, getProducts } from '@/lib/api'
import { notFound } from 'next/navigation'
import Link from 'next/link'
import { ChevronRight } from 'lucide-react'
import { PaginatedResponse, Product } from '@/lib/types'
import CategoryClient from './CategoryClient'

interface PageProps {
  params: Promise<{ slug: string }>
}

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

export async function generateMetadata({ params }: PageProps): Promise<Metadata> {
  const { slug } = await params
  const categories = await getCategories().catch(() => [])
  const category = categories.find((c) => c.slug === slug || String(c.id) === slug)
  if (!category) return {}
  return {
    title: `${category.name} — Tiziano`,
    description: category.description ?? `Productos de ${category.name} para peluquería profesional.`,
  }
}

export default async function CategoryPage({ params }: PageProps) {
  const { slug } = await params

  const categories = await getCategories().catch(() => [])
  const category = categories.find((c) => c.slug === slug || String(c.id) === slug)

  if (!category) notFound()

  const initialData = await getProducts({ category_id: category.id, page: 1 }).catch(() => emptyPaginated)

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
      <Suspense>
        <CategoryClient initialData={initialData} category={category} />
      </Suspense>
    </>
  )
}
