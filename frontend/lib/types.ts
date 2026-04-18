export const DISCOUNT_THRESHOLD = 150_000
export const DISCOUNT_RATE = 0.20

export interface Category {
  id: number
  name: string
  slug: string
  description: string | null
}

export interface Brand {
  id: number
  name: string
  slug: string
  description: string | null
  logo_url: string | null
  is_featured?: boolean
}

export interface Product {
  id: number
  /** Slug para URL SEO (/productos/mi-producto-123); opcional en catálogo legacy */
  slug?: string | null
  name: string
  description: string | null
  sku: string | null
  price: string | number
  is_featured?: boolean
  current_stock: number
  minimum_stock: number
  supplier_name: string | null
  category_id: number | null
  brand_id: number | null
  category: Category | null
  brand: Brand | null
  /** Primera imagen pública (inventario distribuidora); URL absoluta desde Laravel */
  image_url?: string | null
  image_urls?: string[]
  peso_gramos?: number | null
  volumen_cm3?: number | null
  created_at: string
  updated_at: string
}

export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number
  to: number
  links: { url: string | null; label: string; active: boolean }[]
}

export interface Slider {
  id: number
  title: string
  subtitle: string | null
  tag: string | null
  cta_text: string
  cta_link: string
  image_url: string | null
  image_mobile_url: string | null
  bg_color: string
  order: number
}

export interface User {
  id: number
  name: string
  email: string
  role: string
}

export interface AuthResponse {
  user: User
  token: string
}

export interface CartItem {
  product: Product
  quantity: number
}

export type ShippingMethod = 'local_pickup' | 'cordoba' | 'national'

export interface ShippingData {
  shipping_name: string
  shipping_phone: string
  shipping_province: string
  shipping_city: string
  shipping_zip: string
  shipping_address: string
  shipping_address_2?: string
}

export interface ShippingQuote {
  available: boolean
  carrier?: string
  cost?: number
  estimated_days?: string
  message?: string
}

export interface OrderRequest {
  payment_method: 'mercadopago' | 'transfer'
  items: { product_id: number; quantity: number; unit_price: number }[]
  notes?: string
  shipping_name: string
  shipping_phone: string
  shipping_province: string
  shipping_city: string
  shipping_zip: string
  shipping_address: string
  shipping_address_2?: string
  shipping_method: ShippingMethod
  shipping_cost?: number
  discount?: number
}

export interface Order {
  id: number
  order_number: string
  status: string
  payment_method: string
  payment_status: string
  mercadopago_preference_id?: string | null
  mercadopago_payment_id?: string | null
  total: number
  discount?: number | null
  shipping_cost?: number | null
  notes?: string
  shipping_name?: string
  shipping_phone?: string
  shipping_province?: string
  shipping_city?: string
  shipping_zip?: string
  shipping_address?: string
  shipping_address_2?: string
  shipping_method?: string
  items: OrderItemData[]
  created_at: string
}

export interface OrderItemData {
  id: number
  product_id: number
  product_name: string
  quantity: number
  unit_price: number
  subtotal: number
}
