'use client'

import Link from 'next/link'
import Image from 'next/image'
import { useRouter } from 'next/navigation'
import { useState, useEffect, useRef, useCallback } from 'react'
import { Menu, X, ChevronDown, Search, ShoppingBag, User, Loader2 } from 'lucide-react'
import { Category, Product } from '@/lib/types'
import { getCategories, searchProducts, formatPrice, priceSinIVA, productPath } from '@/lib/api'
import { useAuth } from '@/lib/AuthContext'
import { useCart } from '@/lib/CartContext'

const navLinks = [
  { label: 'Inicio', href: '/' },
  { label: 'Productos', href: '/productos', hasDropdown: true },
  { label: 'Nosotros', href: '/nosotros' },
  { label: 'Contacto', href: '/contacto' },
]

export default function Header() {
  const { user, isAuthenticated, logout } = useAuth()
  const { cartCount } = useCart()
  const router = useRouter()
  const [mobileOpen, setMobileOpen] = useState(false)
  const [dropdownOpen, setDropdownOpen] = useState(false)
  const [userMenuOpen, setUserMenuOpen] = useState(false)
  const [searchOpen, setSearchOpen] = useState(false)
  const [searchQuery, setSearchQuery] = useState('')
  const [searchResults, setSearchResults] = useState<Product[]>([])
  const [searchLoading, setSearchLoading] = useState(false)
  const [scrolled, setScrolled] = useState(false)
  const [categories, setCategories] = useState<Category[]>([])
  const userMenuRef = useRef<HTMLDivElement>(null)
  const searchInputRef = useRef<HTMLInputElement>(null)
  const searchContainerRef = useRef<HTMLDivElement>(null)
  const debounceRef = useRef<ReturnType<typeof setTimeout>>(null)

  const handleSearch = useCallback(() => {
    const q = searchQuery.trim()
    if (!q) return
    setSearchOpen(false)
    setSearchResults([])
    setMobileOpen(false)
    setSearchQuery('')
    router.push(`/productos?search=${encodeURIComponent(q)}`)
  }, [searchQuery, router])

  const closeSearch = useCallback(() => {
    setSearchOpen(false)
    setSearchQuery('')
    setSearchResults([])
  }, [])

  useEffect(() => {
    if (debounceRef.current) clearTimeout(debounceRef.current)
    const q = searchQuery.trim()
    if (q.length < 2) { setSearchResults([]); return }
    setSearchLoading(true)
    debounceRef.current = setTimeout(() => {
      searchProducts(q)
        .then(setSearchResults)
        .catch(() => setSearchResults([]))
        .finally(() => setSearchLoading(false))
    }, 300)
    return () => { if (debounceRef.current) clearTimeout(debounceRef.current) }
  }, [searchQuery])

  useEffect(() => {
    function handleClickOutsideSearch(e: MouseEvent) {
      if (searchContainerRef.current && !searchContainerRef.current.contains(e.target as Node)) {
        closeSearch()
      }
    }
    if (searchOpen) {
      document.addEventListener('mousedown', handleClickOutsideSearch)
      return () => document.removeEventListener('mousedown', handleClickOutsideSearch)
    }
  }, [searchOpen, closeSearch])

  useEffect(() => {
    function handleClickOutside(e: MouseEvent) {
      if (userMenuRef.current && !userMenuRef.current.contains(e.target as Node)) {
        setUserMenuOpen(false)
      }
    }
    document.addEventListener('mousedown', handleClickOutside)
    return () => document.removeEventListener('mousedown', handleClickOutside)
  }, [])

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
          <Link href="/" className="flex items-center gap-3">
            <Image
              src="/images/tiziano-logo-final.jpeg"
              alt="Tiziano Peluquería & Spa"
              width={48}
              height={48}
              className="rounded-full object-cover"
              priority
            />
            <div className="flex flex-col leading-none">
              <span
                style={{ fontFamily: 'var(--font-display)', color: 'var(--color-dark)', fontSize: '1.5rem', fontWeight: 700, fontStyle: 'italic' }}
              >
                Tiziano
              </span>
              <span
                style={{ color: 'var(--color-primary)', fontSize: '0.65rem', letterSpacing: '0.25em', textTransform: 'uppercase', fontWeight: 600 }}
              >
                Artículos de Peluquería
              </span>
            </div>
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
                    className="flex items-center gap-1 font-semibold text-sm uppercase tracking-wider py-2"
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

                  {/* Invisible bridge so hover isn't lost crossing the gap */}
                  {dropdownOpen && <div className="absolute left-0 top-full h-2 w-full" />}

                  {dropdownOpen && (
                    <div
                      className="absolute top-[calc(100%+8px)] left-1/2 -translate-x-1/2 z-50 p-5"
                      style={{
                        background: 'var(--color-white)',
                        border: '1px solid var(--color-border)',
                        boxShadow: '0 12px 40px rgba(51,51,51,0.14)',
                        width: 'max-content',
                        maxWidth: '680px',
                      }}
                    >
                      <Link
                        href="/productos"
                        className="block px-4 py-2.5 mb-3 text-sm font-semibold uppercase tracking-wider text-center"
                        style={{
                          color: 'var(--color-white)',
                          background: 'var(--color-dark)',
                        }}
                      >
                        Ver todo el catálogo
                      </Link>

                      <div
                        className="grid gap-x-6 gap-y-1"
                        style={{
                          gridTemplateColumns: `repeat(${Math.min(3, Math.ceil(categories.length / 8) || 1)}, minmax(160px, 1fr))`,
                        }}
                      >
                        {categories.map((cat) => (
                          <Link
                            key={cat.id}
                            href={`/categorias/${cat.slug || cat.id}`}
                            className="block px-3 py-1.5 text-sm rounded transition-colors hover:bg-[var(--color-cream)]"
                            style={{ color: 'var(--color-dark-soft)', whiteSpace: 'nowrap' }}
                          >
                            {cat.name}
                          </Link>
                        ))}
                      </div>
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
            {/* Search - Desktop */}
            <div className="hidden lg:flex items-center relative" ref={searchContainerRef}>
              {searchOpen ? (
                <div className="relative">
                  <div className="flex items-center gap-2">
                    <div className="relative">
                      <input
                        ref={searchInputRef}
                        type="text"
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                        onKeyDown={(e) => {
                          if (e.key === 'Enter') handleSearch()
                          if (e.key === 'Escape') closeSearch()
                        }}
                        placeholder="Buscar productos..."
                        autoFocus
                        className="w-64 pl-3 pr-8 py-1.5 text-sm outline-none"
                        style={{
                          border: '1px solid var(--color-border)',
                          background: 'var(--color-bg)',
                          color: 'var(--color-dark)',
                        }}
                      />
                      {searchLoading && (
                        <Loader2
                          size={14}
                          className="absolute right-2.5 top-1/2 -translate-y-1/2 animate-spin"
                          style={{ color: 'var(--color-dark-soft)' }}
                        />
                      )}
                    </div>
                    <button
                      onClick={closeSearch}
                      className="p-1.5 hover:opacity-60"
                      style={{ color: 'var(--color-dark)' }}
                    >
                      <X size={18} />
                    </button>
                  </div>

                  {/* Live results dropdown */}
                  {searchQuery.trim().length >= 2 && (
                    <div
                      className="absolute right-0 top-full mt-2 z-[60] w-80 max-h-[420px] overflow-y-auto"
                      style={{
                        background: 'var(--color-white)',
                        border: '1px solid var(--color-border)',
                        boxShadow: '0 12px 40px rgba(51,51,51,0.14)',
                      }}
                    >
                      {searchResults.length > 0 ? (
                        <>
                          {searchResults.map((product) => (
                            <Link
                              key={product.id}
                              href={productPath(product)}
                              onClick={closeSearch}
                              className="flex items-center gap-3 px-4 py-3 transition-colors"
                              style={{ borderBottom: '1px solid var(--color-border)' }}
                              onMouseEnter={(e) => (e.currentTarget.style.background = 'var(--color-bg)')}
                              onMouseLeave={(e) => (e.currentTarget.style.background = 'transparent')}
                            >
                              {product.image_url ? (
                                <img
                                  src={product.image_url}
                                  alt={product.name}
                                  className="rounded object-contain flex-shrink-0"
                                  style={{ width: 48, height: 48 }}
                                />
                              ) : (
                                <div
                                  className="flex-shrink-0 rounded flex items-center justify-center"
                                  style={{ width: 48, height: 48, background: 'var(--color-bg)' }}
                                >
                                  <ShoppingBag size={20} style={{ color: 'var(--color-dark-soft)' }} />
                                </div>
                              )}
                              <div className="flex-1 min-w-0">
                                <p
                                  className="text-sm font-medium truncate"
                                  style={{ color: 'var(--color-dark)' }}
                                >
                                  {product.name}
                                </p>
                                <p className="text-xs" style={{ color: 'var(--color-dark-soft)' }}>
                                  {product.brand?.name}
                                </p>
                              </div>
                              <div className="text-right flex-shrink-0">
                                <span
                                  className="text-sm font-semibold block"
                                  style={{ color: 'var(--color-primary)' }}
                                >
                                  {formatPrice(product.price)}
                                </span>
                                <span className="text-[10px] block" style={{ color: '#999' }}>
                                  Sin imp. {priceSinIVA(product.price)}
                                </span>
                              </div>
                            </Link>
                          ))}
                          <button
                            onClick={handleSearch}
                            className="w-full py-3 text-xs font-semibold uppercase tracking-wider text-center transition-colors"
                            style={{ color: 'var(--color-primary)' }}
                            onMouseEnter={(e) => (e.currentTarget.style.background = 'var(--color-bg)')}
                            onMouseLeave={(e) => (e.currentTarget.style.background = 'transparent')}
                          >
                            Ver todos los resultados
                          </button>
                        </>
                      ) : !searchLoading ? (
                        <div className="px-4 py-6 text-center">
                          <p className="text-sm" style={{ color: 'var(--color-dark-soft)' }}>
                            No se encontraron productos
                          </p>
                        </div>
                      ) : null}
                    </div>
                  )}
                </div>
              ) : (
                <button
                  onClick={() => setSearchOpen(true)}
                  style={{ color: 'var(--color-dark)' }}
                  className="p-2 hover:opacity-60"
                >
                  <Search size={20} />
                </button>
              )}
            </div>
            <Link
              href="/carrito"
              style={{ color: 'var(--color-dark)' }}
              className="p-2 hover:opacity-60 relative"
            >
              <ShoppingBag size={20} />
              {cartCount > 0 && (
                <span
                  className="absolute -top-0.5 -right-0.5 flex items-center justify-center text-[10px] font-bold leading-none rounded-full"
                  style={{
                    background: 'var(--color-primary)',
                    color: 'var(--color-dark)',
                    width: 18,
                    height: 18,
                  }}
                >
                  {cartCount > 99 ? '99+' : cartCount}
                </span>
              )}
            </Link>

            {/* Auth - Desktop */}
            <div className="hidden lg:block relative" ref={userMenuRef}>
              {isAuthenticated ? (
                <>
                  <button
                    onClick={() => setUserMenuOpen(!userMenuOpen)}
                    className="flex items-center gap-2 p-2 hover:opacity-60"
                    style={{ color: 'var(--color-dark)' }}
                  >
                    <User size={20} />
                    <span className="text-xs font-semibold uppercase tracking-wider max-w-[100px] truncate">
                      {user?.name?.split(' ')[0]}
                    </span>
                  </button>
                  {userMenuOpen && (
                    <div
                      className="absolute right-0 top-full mt-2 z-50 py-2 min-w-[160px]"
                      style={{
                        background: 'var(--color-white)',
                        border: '1px solid var(--color-border)',
                        boxShadow: '0 8px 24px rgba(51,51,51,0.12)',
                      }}
                    >
                      <Link
                        href="/mi-cuenta"
                        className="block px-4 py-2 text-sm hover:opacity-70"
                        style={{ color: 'var(--color-dark)' }}
                        onClick={() => setUserMenuOpen(false)}
                      >
                        Mi cuenta
                      </Link>
                      <button
                        onClick={async () => { setUserMenuOpen(false); await logout() }}
                        className="block w-full text-left px-4 py-2 text-sm hover:opacity-70"
                        style={{ color: 'var(--color-dark)' }}
                      >
                        Cerrar sesión
                      </button>
                    </div>
                  )}
                </>
              ) : (
                <Link
                  href="/ingresar"
                  className="flex items-center gap-1.5 p-2 hover:opacity-60 text-xs font-semibold uppercase tracking-wider"
                  style={{ color: 'var(--color-dark)' }}
                >
                  <User size={20} />
                  Ingresar
                </Link>
              )}
            </div>

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
            {/* Mobile search */}
            <div className="mb-4 pb-4 border-b" style={{ borderColor: 'var(--color-border)' }}>
              <div className="flex items-center gap-2">
                <div className="relative flex-1">
                  <Search
                    size={16}
                    className="absolute left-3 top-1/2 -translate-y-1/2"
                    style={{ color: 'var(--color-dark-soft)' }}
                  />
                  <input
                    type="text"
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    onKeyDown={(e) => { if (e.key === 'Enter') handleSearch() }}
                    placeholder="Buscar productos..."
                    className="w-full pl-9 pr-8 py-2.5 text-sm outline-none"
                    style={{
                      border: '1px solid var(--color-border)',
                      background: 'var(--color-bg)',
                      color: 'var(--color-dark)',
                    }}
                  />
                  {searchLoading && (
                    <Loader2
                      size={14}
                      className="absolute right-2.5 top-1/2 -translate-y-1/2 animate-spin"
                      style={{ color: 'var(--color-dark-soft)' }}
                    />
                  )}
                </div>
                <button
                  onClick={handleSearch}
                  className="px-4 py-2.5 text-xs font-semibold uppercase tracking-wider"
                  style={{ background: 'var(--color-dark)', color: 'var(--color-white)' }}
                >
                  Buscar
                </button>
              </div>

              {/* Mobile live results */}
              {searchQuery.trim().length >= 2 && (
                <div
                  className="mt-2 max-h-[300px] overflow-y-auto"
                  style={{
                    border: '1px solid var(--color-border)',
                    background: 'var(--color-white)',
                  }}
                >
                  {searchResults.length > 0 ? (
                    <>
                      {searchResults.map((product) => (
                        <Link
                          key={product.id}
                          href={productPath(product)}
                          onClick={() => { closeSearch(); setMobileOpen(false) }}
                          className="flex items-center gap-3 px-3 py-2.5"
                          style={{ borderBottom: '1px solid var(--color-border)' }}
                        >
                          {product.image_url ? (
                            <img
                              src={product.image_url}
                              alt={product.name}
                              className="rounded object-contain flex-shrink-0"
                              style={{ width: 40, height: 40 }}
                            />
                          ) : (
                            <div
                              className="flex-shrink-0 rounded flex items-center justify-center"
                              style={{ width: 40, height: 40, background: 'var(--color-bg)' }}
                            >
                              <ShoppingBag size={16} style={{ color: 'var(--color-dark-soft)' }} />
                            </div>
                          )}
                          <div className="flex-1 min-w-0">
                            <p
                              className="text-sm font-medium truncate"
                              style={{ color: 'var(--color-dark)' }}
                            >
                              {product.name}
                            </p>
                            <p className="text-xs" style={{ color: 'var(--color-dark-soft)' }}>
                              {product.brand?.name}
                            </p>
                          </div>
                          <div className="text-right flex-shrink-0">
                            <span
                              className="text-sm font-semibold block"
                              style={{ color: 'var(--color-primary)' }}
                            >
                              {formatPrice(product.price)}
                            </span>
                            <span className="text-[10px] block" style={{ color: '#999' }}>
                              Sin imp. {priceSinIVA(product.price)}
                            </span>
                          </div>
                        </Link>
                      ))}
                      <button
                        onClick={handleSearch}
                        className="w-full py-2.5 text-xs font-semibold uppercase tracking-wider text-center"
                        style={{ color: 'var(--color-primary)' }}
                      >
                        Ver todos los resultados
                      </button>
                    </>
                  ) : !searchLoading ? (
                    <div className="px-4 py-4 text-center">
                      <p className="text-sm" style={{ color: 'var(--color-dark-soft)' }}>
                        No se encontraron productos
                      </p>
                    </div>
                  ) : null}
                </div>
              )}
            </div>

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
              className="block py-3 font-semibold text-sm uppercase tracking-wider border-b"
              style={{ color: 'var(--color-dark)', borderColor: 'var(--color-border)' }}
              onClick={() => setMobileOpen(false)}
            >
              Contacto
            </Link>
            {isAuthenticated ? (
              <>
                <Link
                  href="/mi-cuenta"
                  className="block py-3 font-semibold text-sm uppercase tracking-wider border-b"
                  style={{ color: 'var(--color-dark)', borderColor: 'var(--color-border)' }}
                  onClick={() => setMobileOpen(false)}
                >
                  Mi cuenta
                </Link>
                <button
                  onClick={async () => { setMobileOpen(false); await logout() }}
                  className="block w-full text-left py-3 font-semibold text-sm uppercase tracking-wider"
                  style={{ color: 'var(--color-dark)' }}
                >
                  Cerrar sesión
                </button>
              </>
            ) : (
              <Link
                href="/ingresar"
                className="block py-3 font-semibold text-sm uppercase tracking-wider"
                style={{ color: 'var(--color-primary)' }}
                onClick={() => setMobileOpen(false)}
              >
                Ingresar / Registrarse
              </Link>
            )}
          </div>
        )}
      </header>
    </>
  )
}
