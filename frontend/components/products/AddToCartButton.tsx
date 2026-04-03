'use client'

import { useState } from 'react'
import { ShoppingBag, Check } from 'lucide-react'
import { Product } from '@/lib/types'
import { useCart } from '@/lib/CartContext'

interface AddToCartButtonProps {
  product: Product
}

export default function AddToCartButton({ product }: AddToCartButtonProps) {
  const { addItem } = useCart()
  const [added, setAdded] = useState(false)
  const inStock = product.current_stock > 0

  const handleAdd = () => {
    if (!inStock) return
    addItem(product)
    setAdded(true)
    setTimeout(() => setAdded(false), 2000)
  }

  return (
    <button
      onClick={handleAdd}
      className="flex-1 flex items-center justify-center gap-2 py-4 text-sm font-bold uppercase tracking-widest transition-all"
      style={{
        background: added ? '#2E7D52' : inStock ? 'var(--color-dark)' : '#ccc',
        color: 'var(--color-white)',
        cursor: inStock ? 'pointer' : 'not-allowed',
      }}
      disabled={!inStock}
    >
      {added ? <Check size={16} /> : <ShoppingBag size={16} />}
      {added ? 'Agregado al carrito' : inStock ? 'Agregar al carrito' : 'Sin stock'}
    </button>
  )
}
