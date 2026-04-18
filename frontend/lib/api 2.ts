import { Brand, Category, PaginatedResponse, Product } from './types'

const BASE_URL = process.env.NEXT_PUBLIC_API_URL ?? 'http://localhost:8000'

async function apiFetch<T>(path: string): Promise<T> {
  const res = await fetch(`${BASE_URL}/api${path}`, {
    headers: { Accept: 'application/json' },
    next: { revalidate: 60 },
  })
  if (!res.ok) throw new Error(`API error ${res.status}: ${path}`)
  return res.json()
}

export async function getProducts(params?: {
  category_id?: number
  brand_id?: number
  brand_slug?: string
  search?: string
  page?: number
}): Promise<PaginatedResponse<Product>> {
  const qs = new URLSearchParams()
  if (params?.category_id) qs.set('category_id', String(params.category_id))
  if (params?.brand_slug) qs.set('brand_slug', params.brand_slug)
  else if (params?.brand_id) qs.set('brand_id', String(params.brand_id))
  if (params?.search) qs.set('search', params.search)
  if (params?.page) qs.set('page', String(params.page))
  const query = qs.toString() ? `?${qs}` : ''
  return apiFetch<PaginatedResponse<Product>>(`/products${query}`)
}

export function productPath(product: Pick<Product, 'id' | 'slug'>): string {
  if (product.slug && String(product.slug).length > 0) {
    return `/productos/${encodeURIComponent(product.slug)}`
  }
  return `/productos/${product.id}`
}

export async function getProduct(identifier: string): Promise<Product> {
  return apiFetch<Product>(`/products/${encodeURIComponent(identifier)}`)
}

export async function getCategories(): Promise<Category[]> {
  return apiFetch<Category[]>('/categories')
}

export async function getBrands(): Promise<Brand[]> {
  return apiFetch<Brand[]>('/brands')
}

export function formatPrice(price: string | number): string {
  const num = typeof price === 'string' ? parseFloat(price) : price
  return new Intl.NumberFormat('es-AR', {
    style: 'currency',
    currency: 'ARS',
    minimumFractionDigits: 0,
  }).format(num)
}
