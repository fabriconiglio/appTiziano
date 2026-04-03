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
}

export interface Product {
  id: number
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

export type HairType = {
  slug: string
  label: string
  description: string
  icon: string
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

export interface OrderRequest {
  payment_method: 'taca_taca' | 'transfer'
  items: { product_id: number; quantity: number; unit_price: number }[]
  notes?: string
}

export interface Order {
  id: number
  order_number: string
  status: string
  payment_method: string
  payment_status: string
  total: number
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
