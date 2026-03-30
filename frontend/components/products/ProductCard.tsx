'use client'

import Link from 'next/link'
import { ShoppingBag, Star } from 'lucide-react'
import { Product } from '@/lib/types'
import { formatPrice } from '@/lib/api'

interface ProductCardProps {
  product: Product
}

export default function ProductCard({ product }: ProductCardProps) {
  const price = formatPrice(product.price)

  return (
    <div
      className="group flex flex-col"
      style={{
        background: 'var(--color-white)',
        border: '1px solid var(--color-border)',
        transition: 'all 0.3s ease',
      }}
      onMouseEnter={(e) => {
        const el = e.currentTarget
        el.style.boxShadow = '0 16px 40px rgba(51,51,51,0.12)'
        el.style.transform = 'translateY(-3px)'
        el.style.borderColor = 'var(--color-primary)'
      }}
      onMouseLeave={(e) => {
        const el = e.currentTarget
        el.style.boxShadow = 'none'
        el.style.transform = 'translateY(0)'
        el.style.borderColor = 'var(--color-border)'
      }}
    >
      {/* Image area */}
      <Link href={`/productos/${product.id}`} className="block relative overflow-hidden" style={{ aspectRatio: '1 / 1' }}>
        {product.image_url ? (
          <img
            src={product.image_url}
            alt={product.name}
            className="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
            loading="lazy"
          />
        ) : (
          <div
            className="w-full h-full flex items-center justify-center"
            style={{ background: 'linear-gradient(135deg, var(--color-cream) 0%, var(--color-primary-light) 100%)' }}
          >
            <div className="text-center px-4">
              <div
                className="w-20 h-20 rounded-full mx-auto mb-3 flex items-center justify-center"
                style={{ background: 'var(--color-primary)', opacity: 0.4 }}
              />
              <span
                className="text-xs uppercase tracking-wider font-semibold"
                style={{ color: 'var(--color-primary-dark)' }}
              >
                {product.brand?.name ?? 'Tiziano'}
              </span>
            </div>
          </div>
        )}

        {/* Stock badge */}
        {product.current_stock <= product.minimum_stock && product.current_stock > 0 && (
          <div
            className="absolute top-3 left-3 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide"
            style={{ background: '#E88080', color: '#fff' }}
          >
            Últimas unidades
          </div>
        )}

        {/* Category badge */}
        {product.category && (
          <div
            className="absolute top-3 right-3 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide"
            style={{ background: 'var(--color-dark)', color: 'var(--color-primary-light)' }}
          >
            {product.category.name}
          </div>
        )}
      </Link>

      {/* Info */}
      <div className="flex flex-col flex-1 p-4">
        {/* Stars decoration */}
        <div className="flex gap-0.5 mb-2">
          {Array.from({ length: 5 }).map((_, i) => (
            <Star
              key={i}
              size={11}
              fill="var(--color-primary)"
              style={{ color: 'var(--color-primary)' }}
            />
          ))}
        </div>

        <Link href={`/productos/${product.id}`}>
          <h3
            className="font-semibold text-sm mb-1 leading-snug line-clamp-2 hover:opacity-70 transition-opacity"
            style={{ color: 'var(--color-dark)', fontFamily: 'var(--font-body)' }}
          >
            {product.name}
          </h3>
        </Link>

        {product.brand && (
          <p className="text-xs mb-3" style={{ color: 'var(--color-primary-dark)', fontStyle: 'italic' }}>
            {product.brand.name}
          </p>
        )}

        <div className="mt-auto flex items-center justify-between gap-2">
          <span
            className="font-bold text-base"
            style={{ color: 'var(--color-dark)', fontFamily: 'var(--font-display)' }}
          >
            {price}
          </span>

          <button
            className="flex items-center gap-1.5 px-3 py-2 text-xs font-semibold uppercase tracking-wider transition-all"
            style={{
              background: 'var(--color-dark)',
              color: 'var(--color-white)',
              border: '1px solid var(--color-dark)',
            }}
            onMouseEnter={(e) => {
              const el = e.currentTarget
              el.style.background = 'var(--color-primary)'
              el.style.borderColor = 'var(--color-primary)'
              el.style.color = 'var(--color-dark)'
            }}
            onMouseLeave={(e) => {
              const el = e.currentTarget
              el.style.background = 'var(--color-dark)'
              el.style.borderColor = 'var(--color-dark)'
              el.style.color = 'var(--color-white)'
            }}
          >
            <ShoppingBag size={13} />
            Agregar
          </button>
        </div>
      </div>
    </div>
  )
}
