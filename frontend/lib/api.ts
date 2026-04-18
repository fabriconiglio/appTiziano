import { AuthResponse, Brand, Category, Order, OrderRequest, PaginatedResponse, Product, ShippingQuote, Slider, User } from './types'

const BASE_URL = process.env.NEXT_PUBLIC_API_URL ?? 'http://localhost:8000'

async function apiFetch<T>(path: string, cacheMode: 'isr' | 'no-store' = 'isr'): Promise<T> {
  const res = await fetch(`${BASE_URL}/api${path}`, {
    headers: { Accept: 'application/json' },
    ...(cacheMode === 'no-store'
      ? { cache: 'no-store' as RequestCache }
      : { next: { revalidate: 60 } }),
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
  featured?: boolean
}): Promise<PaginatedResponse<Product>> {
  const qs = new URLSearchParams()
  if (params?.category_id) qs.set('category_id', String(params.category_id))
  if (params?.brand_slug) qs.set('brand_slug', params.brand_slug)
  else if (params?.brand_id) qs.set('brand_id', String(params.brand_id))
  if (params?.search) qs.set('search', params.search)
  if (params?.page) qs.set('page', String(params.page))
  if (params?.featured) qs.set('featured', '1')
  const query = qs.toString() ? `?${qs}` : ''
  const cacheMode = params?.featured ? 'no-store' : 'isr'
  return apiFetch<PaginatedResponse<Product>>(`/products${query}`, cacheMode)
}

/** Ruta pública del producto (slug si existe; si no, id numérico). */
export function productPath(product: Pick<Product, 'id' | 'slug'>): string {
  if (product.slug && String(product.slug).length > 0) {
    return `/productos/${encodeURIComponent(product.slug)}`
  }
  return `/productos/${product.id}`
}

export async function getProduct(identifier: string): Promise<Product> {
  return apiFetch<Product>(`/products/${encodeURIComponent(identifier)}`)
}

export async function searchProducts(query: string, limit = 6): Promise<Product[]> {
  const qs = new URLSearchParams({ search: query, per_page: String(limit) })
  const res = await apiFetch<PaginatedResponse<Product>>(`/products?${qs}`, 'no-store')
  return res.data
}

export async function getCategories(): Promise<Category[]> {
  return apiFetch<Category[]>('/categories')
}

export async function getBrands(params?: { featured?: boolean }): Promise<Brand[]> {
  const qs = new URLSearchParams()
  if (params?.featured) qs.set('featured', '1')
  const query = qs.toString() ? `?${qs}` : ''
  return apiFetch<Brand[]>(`/brands${query}`)
}

export async function getSliders(): Promise<Slider[]> {
  return apiFetch<Slider[]>('/sliders')
}

export function formatPrice(price: string | number): string {
  const num = typeof price === 'string' ? Number.parseFloat(price) : price
  return new Intl.NumberFormat('es-AR', {
    style: 'currency',
    currency: 'ARS',
    minimumFractionDigits: 0,
  }).format(num)
}

export function priceSinIVA(price: string | number): string {
  const num = typeof price === 'string' ? Number.parseFloat(price) : price
  return formatPrice(Math.round(num / 1.21))
}

async function authFetch<T>(path: string, options: RequestInit = {}, token?: string): Promise<T> {
  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
    Accept: 'application/json',
    ...(token ? { Authorization: `Bearer ${token}` } : undefined),
  }
  const res = await fetch(`${BASE_URL}/api${path}`, {
    ...options,
    headers: { ...headers, ...((options.headers as Record<string, string>) || undefined) },
    cache: 'no-store',
  })
  const body = await res.json()
  if (!res.ok) throw body
  return body as T
}

export async function registerUser(
  name: string,
  email: string,
  password: string,
  password_confirmation: string,
): Promise<AuthResponse> {
  return authFetch<AuthResponse>('/auth/register', {
    method: 'POST',
    body: JSON.stringify({ name, email, password, password_confirmation }),
  })
}

export async function loginUser(email: string, password: string): Promise<AuthResponse> {
  return authFetch<AuthResponse>('/auth/login', {
    method: 'POST',
    body: JSON.stringify({ email, password }),
  })
}

export async function logoutUser(token: string): Promise<void> {
  await authFetch('/auth/logout', { method: 'POST' }, token)
}

export async function getMe(token: string): Promise<User> {
  return authFetch<User>('/auth/me', {}, token)
}

export async function googleLogin(credential: string): Promise<AuthResponse> {
  return authFetch<AuthResponse>('/auth/google', {
    method: 'POST',
    body: JSON.stringify({ credential }),
  })
}

export async function getShippingQuote(
  shippingZip: string,
  items: { product_id: number; quantity: number }[],
): Promise<ShippingQuote> {
  const res = await fetch(`${BASE_URL}/api/shipping/quote`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    body: JSON.stringify({ shipping_zip: shippingZip, items }),
    cache: 'no-store',
  })
  if (!res.ok) throw new Error(`Shipping quote error ${res.status}`)
  return res.json()
}

export async function createOrder(data: OrderRequest, token: string): Promise<{ order: Order; checkout_url?: string }> {
  return authFetch<{ order: Order; checkout_url?: string }>('/orders', {
    method: 'POST',
    body: JSON.stringify(data),
  }, token)
}

export async function getOrders(token: string): Promise<Order[]> {
  return authFetch<Order[]>('/orders', {}, token)
}

export async function getOrder(id: number, token: string): Promise<Order> {
  return authFetch<Order>(`/orders/${id}`, {}, token)
}

export async function submitArrepentimiento(data: {
  name: string
  email: string
  order_number: string
  reason?: string
}): Promise<{ message: string; code: string }> {
  return authFetch<{ message: string; code: string }>('/arrepentimiento', {
    method: 'POST',
    body: JSON.stringify(data),
  })
}
