'use client'

import { useState } from 'react'
import { ChevronDown } from 'lucide-react'
import { Category, Brand } from '@/lib/types'

interface FilterSidebarProps {
  categories: Category[]
  brands: Brand[]
  selectedCategory: number | null
  selectedBrand: number | null
  onCategoryChange: (id: number | null) => void
  onBrandChange: (id: number | null) => void
}

function FilterGroup({
  title,
  children,
}: {
  title: string
  children: React.ReactNode
}) {
  const [open, setOpen] = useState(true)

  return (
    <div style={{ borderBottom: '1px solid var(--color-border)', paddingBottom: '1.25rem', marginBottom: '1.25rem' }}>
      <button
        onClick={() => setOpen(!open)}
        className="flex items-center justify-between w-full mb-3"
      >
        <span
          className="text-xs font-bold uppercase tracking-widest"
          style={{ color: 'var(--color-dark)' }}
        >
          {title}
        </span>
        <ChevronDown
          size={14}
          style={{
            color: 'var(--color-dark-soft)',
            transform: open ? 'rotate(180deg)' : 'rotate(0)',
            transition: 'transform 0.2s',
          }}
        />
      </button>
      {open && <div>{children}</div>}
    </div>
  )
}

export default function FilterSidebar({
  categories,
  brands,
  selectedCategory,
  selectedBrand,
  onCategoryChange,
  onBrandChange,
}: FilterSidebarProps) {
  return (
    <aside
      className="w-full lg:w-60 shrink-0"
    >
      <div
        className="p-5"
        style={{ background: 'var(--color-white)', border: '1px solid var(--color-border)' }}
      >
        <div className="flex items-center justify-between mb-5">
          <h3
            className="font-bold text-sm uppercase tracking-widest"
            style={{ color: 'var(--color-dark)', fontFamily: 'var(--font-display)' }}
          >
            Filtros
          </h3>
          {(selectedCategory || selectedBrand) && (
            <button
              className="text-xs underline"
              style={{ color: 'var(--color-primary-dark)' }}
              onClick={() => {
                onCategoryChange(null)
                onBrandChange(null)
              }}
            >
              Limpiar
            </button>
          )}
        </div>

        <FilterGroup title="Categoría">
          <ul className="space-y-1.5">
            <li>
              <button
                className="text-sm w-full text-left px-2 py-1 transition-colors"
                style={{
                  color: selectedCategory === null ? 'var(--color-dark)' : 'var(--color-dark-soft)',
                  fontWeight: selectedCategory === null ? 700 : 400,
                  background: selectedCategory === null ? 'var(--color-cream)' : 'transparent',
                }}
                onClick={() => onCategoryChange(null)}
              >
                Todos
              </button>
            </li>
            {categories.map((cat) => (
              <li key={cat.id}>
                <button
                  className="text-sm w-full text-left px-2 py-1 transition-colors"
                  style={{
                    color: selectedCategory === cat.id ? 'var(--color-dark)' : 'var(--color-dark-soft)',
                    fontWeight: selectedCategory === cat.id ? 700 : 400,
                    background: selectedCategory === cat.id ? 'var(--color-cream)' : 'transparent',
                  }}
                  onClick={() => onCategoryChange(cat.id)}
                >
                  {cat.name}
                </button>
              </li>
            ))}
          </ul>
        </FilterGroup>

        {brands.length > 0 && (
          <FilterGroup title="Marca">
            <ul className="space-y-1.5">
              <li>
                <button
                  className="text-sm w-full text-left px-2 py-1 transition-colors"
                  style={{
                    color: selectedBrand === null ? 'var(--color-dark)' : 'var(--color-dark-soft)',
                    fontWeight: selectedBrand === null ? 700 : 400,
                    background: selectedBrand === null ? 'var(--color-cream)' : 'transparent',
                  }}
                  onClick={() => onBrandChange(null)}
                >
                  Todas
                </button>
              </li>
              {brands.map((b) => (
                <li key={b.id}>
                  <button
                    className="text-sm w-full text-left px-2 py-1 transition-colors"
                    style={{
                      color: selectedBrand === b.id ? 'var(--color-dark)' : 'var(--color-dark-soft)',
                      fontWeight: selectedBrand === b.id ? 700 : 400,
                      background: selectedBrand === b.id ? 'var(--color-cream)' : 'transparent',
                    }}
                    onClick={() => onBrandChange(b.id)}
                  >
                    {b.name}
                  </button>
                </li>
              ))}
            </ul>
          </FilterGroup>
        )}
      </div>
    </aside>
  )
}
