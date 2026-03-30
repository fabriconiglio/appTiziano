'use client'

import Link from 'next/link'
import { useState, useEffect } from 'react'
import { Menu, X, ChevronDown, Search, ShoppingBag } from 'lucide-react'
import { Category } from '@/lib/types'
import { getCategories } from '@/lib/api'

const navLinks = [
  { label: 'Inicio', href: '/' },
  { label: 'Productos', href: '/productos', hasDropdown: true },
  { label: 'Nosotros', href: '/nosotros' },
  { label: 'Contacto', href: '/contacto' },
]

export default function Header() {
  const [mobileOpen, setMobileOpen] = useState(false)
  const [dropdownOpen, setDropdownOpen] = useState(false)
  const [scrolled, setScrolled] = useState(false)
  const [categories, setCategories] = useState<Category[]>([])

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 40)
    window.addEventListener('scroll', onScroll)
    return () => window.removeEventListener('scroll', onScroll)
  }, [])

  useEffect(() => {
    getCategories().then(setCategories).catch(() => {})
  }, [])

  return (
    <>
      {/* Top bar */}
      <div
        style={{ background: 'var(--color-dark)', color: 'var(--color-primary-light)' }}
        className="text-center text-xs py-2 tracking-widest uppercase font-semibold"
      >
        Envíos a todo el país · Atención profesional
      </div>

      {/* Main header */}
      <header
        style={{
          background: 'var(--color-white)',
          borderBottom: `1px solid var(--color-border)`,
          boxShadow: scrolled ? '0 2px 20px rgba(51,51,51,0.08)' : 'none',
          transition: 'box-shadow 0.3s ease',
        }}
        className="sticky top-0 z-50"
      >
        <div className="max-w-7xl mx-auto px-6 flex items-center justify-between h-20">
          {/* Logo */}
          <Link href="/" className="flex flex-col leading-none">
            <span
              style={{ fontFamily: 'var(--font-display)', color: 'var(--color-dark)', fontSize: '1.5rem', fontWeight: 700, fontStyle: 'italic' }}
            >
              Tiziano
            </span>
            <span
              style={{ color: 'var(--color-primary)', fontSize: '0.65rem', letterSpacing: '0.25em', textTransform: 'uppercase', fontWeight: 600 }}
            >
              Peluquería Profesional
            </span>
          </Link>

          {/* Desktop nav */}
          <nav className="hidden lg:flex items-center gap-8">
            {navLinks.map((link) =>
              link.hasDropdown ? (
                <div
                  key={link.href}
                  className="relative"
                  onMouseEnter={() => setDropdownOpen(true)}
                  onMouseLeave={() => setDropdownOpen(false)}
                >
                  <button
                    className="flex items-center gap-1 font-semibold text-sm uppercase tracking-wider"
                    style={{ color: 'var(--color-dark)', letterSpacing: '0.1em' }}
                  >
                    {link.label}
                    <ChevronDown
                      size={14}
                      style={{
                        transform: dropdownOpen ? 'rotate(180deg)' : 'rotate(0)',
                        transition: 'transform 0.2s',
                      }}
                    />
                  </button>

                  {dropdownOpen && (
                    <div
                      className="absolute top-full left-0 mt-2 min-w-56 py-2 z-50"
                      style={{
                        background: 'var(--color-white)',
                        border: `1px solid var(--color-border)`,
                        boxShadow: '0 8px 30px rgba(51,51,51,0.12)',
                      }}
                    >
                      <Link
                        href="/productos"
                        className="block px-5 py-2.5 text-sm font-semibold uppercase tracking-wider"
                        style={{ color: 'var(--color-primary)', borderBottom: `1px solid var(--color-border)` }}
                      >
                        Ver todos
                      </Link>
                      {categories.map((cat) => (
                        <Link
                          key={cat.id}
                          href={`/categorias/${cat.slug || cat.id}`}
                          className="block px-5 py-2 text-sm hover:bg-[var(--color-cream)]"
                          style={{ color: 'var(--color-dark-soft)' }}
                        >
                          {cat.name}
                        </Link>
                      ))}
                    </div>
                  )}
                </div>
              ) : (
                <Link
                  key={link.href}
                  href={link.href}
                  className="font-semibold text-sm uppercase tracking-wider"
                  style={{ color: 'var(--color-dark)', letterSpacing: '0.1em' }}
                >
                  {link.label}
                </Link>
              )
            )}
          </nav>

          {/* Actions */}
          <div className="flex items-center gap-4">
            <button
              style={{ color: 'var(--color-dark)' }}
              className="p-2 hidden lg:block hover:opacity-60"
            >
              <Search size={20} />
            </button>
            <button
              style={{ color: 'var(--color-dark)' }}
              className="p-2 hover:opacity-60"
            >
              <ShoppingBag size={20} />
            </button>
            <button
              className="lg:hidden p-2"
              style={{ color: 'var(--color-dark)' }}
              onClick={() => setMobileOpen(!mobileOpen)}
            >
              {mobileOpen ? <X size={22} /> : <Menu size={22} />}
            </button>
          </div>
        </div>

        {/* Mobile menu */}
        {mobileOpen && (
          <div
            style={{ background: 'var(--color-white)', borderTop: `1px solid var(--color-border)` }}
            className="lg:hidden px-6 pb-6 pt-4"
          >
            <Link
              href="/"
              className="block py-3 font-semibold text-sm uppercase tracking-wider border-b"
              style={{ color: 'var(--color-dark)', borderColor: 'var(--color-border)' }}
              onClick={() => setMobileOpen(false)}
            >
              Inicio
            </Link>
            <Link
              href="/productos"
              className="block py-3 font-semibold text-sm uppercase tracking-wider border-b"
              style={{ color: 'var(--color-dark)', borderColor: 'var(--color-border)' }}
              onClick={() => setMobileOpen(false)}
            >
              Productos
            </Link>
            {categories.map((cat) => (
              <Link
                key={cat.id}
                href={`/categorias/${cat.slug || cat.id}`}
                className="block py-2.5 pl-4 text-sm border-b"
                style={{ color: 'var(--color-dark-soft)', borderColor: 'var(--color-border)' }}
                onClick={() => setMobileOpen(false)}
              >
                {cat.name}
              </Link>
            ))}
            <Link
              href="/nosotros"
              className="block py-3 font-semibold text-sm uppercase tracking-wider border-b"
              style={{ color: 'var(--color-dark)', borderColor: 'var(--color-border)' }}
              onClick={() => setMobileOpen(false)}
            >
              Nosotros
            </Link>
            <Link
              href="/contacto"
              className="block py-3 font-semibold text-sm uppercase tracking-wider"
              style={{ color: 'var(--color-dark)' }}
              onClick={() => setMobileOpen(false)}
            >
              Contacto
            </Link>
          </div>
        )}
      </header>
    </>
  )
}
