'use client'

import { createContext, useContext, useState, useEffect, useCallback, useMemo, type ReactNode } from 'react'
import { Product, CartItem, DISCOUNT_THRESHOLD, DISCOUNT_RATE } from './types'

interface CartContextType {
  items: CartItem[]
  cartCount: number
  cartTotal: number
  discount: number
  finalTotal: number
  addItem: (product: Product, quantity?: number) => void
  removeItem: (productId: number) => void
  updateQuantity: (productId: number, quantity: number) => void
  clearCart: () => void
}

const CartContext = createContext<CartContextType | undefined>(undefined)

const CART_KEY = 'tiziano_cart'

function loadCart(): CartItem[] {
  if (typeof window === 'undefined') return []
  try {
    const raw = localStorage.getItem(CART_KEY)
    return raw ? JSON.parse(raw) : []
  } catch {
    return []
  }
}

function saveCart(items: CartItem[]) {
  localStorage.setItem(CART_KEY, JSON.stringify(items))
}

function priceToNumber(price: string | number): number {
  return typeof price === 'string' ? parseFloat(price) : price
}

export function CartProvider({ children }: { children: ReactNode }) {
  const [items, setItems] = useState<CartItem[]>([])
  const [hydrated, setHydrated] = useState(false)

  useEffect(() => {
    setItems(loadCart())
    setHydrated(true)
  }, [])

  useEffect(() => {
    if (hydrated) saveCart(items)
  }, [items, hydrated])

  const addItem = useCallback((product: Product, quantity = 1) => {
    setItems((prev) => {
      const existing = prev.find((i) => i.product.id === product.id)
      const maxStock = product.current_stock
      if (existing) {
        const newQty = Math.min(existing.quantity + quantity, maxStock)
        return prev.map((i) =>
          i.product.id === product.id ? { ...i, quantity: newQty } : i,
        )
      }
      return [...prev, { product, quantity: Math.min(quantity, maxStock) }]
    })
  }, [])

  const removeItem = useCallback((productId: number) => {
    setItems((prev) => prev.filter((i) => i.product.id !== productId))
  }, [])

  const updateQuantity = useCallback((productId: number, quantity: number) => {
    if (quantity <= 0) {
      setItems((prev) => prev.filter((i) => i.product.id !== productId))
      return
    }
    setItems((prev) =>
      prev.map((i) => {
        if (i.product.id !== productId) return i
        const capped = Math.min(quantity, i.product.current_stock)
        return { ...i, quantity: capped }
      }),
    )
  }, [])

  const clearCart = useCallback(() => setItems([]), [])

  const cartCount = useMemo(() => items.reduce((sum, i) => sum + i.quantity, 0), [items])
  const cartTotal = useMemo(() => items.reduce((sum, i) => sum + priceToNumber(i.product.price) * i.quantity, 0), [items])
  const discount = useMemo(() => cartTotal > DISCOUNT_THRESHOLD ? cartTotal * DISCOUNT_RATE : 0, [cartTotal])
  const finalTotal = useMemo(() => cartTotal - discount, [cartTotal, discount])

  const value = useMemo(
    () => ({ items, cartCount, cartTotal, discount, finalTotal, addItem, removeItem, updateQuantity, clearCart }),
    [items, cartCount, cartTotal, discount, finalTotal, addItem, removeItem, updateQuantity, clearCart],
  )

  return (
    <CartContext.Provider value={value}>
      {children}
    </CartContext.Provider>
  )
}

export function useCart(): CartContextType {
  const ctx = useContext(CartContext)
  if (!ctx) throw new Error('useCart must be used within CartProvider')
  return ctx
}
