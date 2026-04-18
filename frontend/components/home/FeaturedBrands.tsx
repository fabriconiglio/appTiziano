import Link from 'next/link'
import { getBrands } from '@/lib/api'

export default async function FeaturedBrands() {
  const brands = await getBrands({ featured: true })

  if (brands.length === 0) return null

  return (
    <section style={{ background: 'var(--color-cream)', padding: '80px 0' }}>
      <div className="max-w-7xl mx-auto px-6">
        {/* Header */}
        <div className="text-center mb-14">
          <p
            className="text-xs uppercase tracking-widest font-semibold mb-3"
            style={{ color: 'var(--color-primary)' }}
          >
            Las mejores marcas
          </p>
          <h2 className="section-title">Marcas Destacadas</h2>
          <div className="decorative-line mt-4 mb-5" />
          <p className="section-subtitle">
            Trabajamos con las marcas líderes en el cuidado profesional del cabello
          </p>
        </div>

        {/* Grid */}
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
          {brands.map((brand) => (
            <Link
              key={brand.id}
              href={`/productos?brand_id=${brand.id}`}
              className="group flex flex-col items-center gap-4 p-6 transition-all"
              style={{
                background: 'var(--color-white)',
                border: '1px solid var(--color-border)',
              }}
            >
              {/* Logo or initial */}
              {brand.logo_url ? (
                <img
                  src={brand.logo_url}
                  alt={brand.name}
                  className="w-14 h-14 object-contain shrink-0 group-hover:scale-110 transition-transform"
                />
              ) : (
                <div
                  className="w-14 h-14 rounded-full flex items-center justify-center text-white text-xl font-bold shrink-0"
                  style={{
                    background: 'linear-gradient(135deg, var(--color-dark) 0%, #555 100%)',
                    fontFamily: 'var(--font-display)',
                    fontStyle: 'italic',
                  }}
                >
                  {brand.name.charAt(0).toUpperCase()}
                </div>
              )}

              <div className="text-center">
                <p
                  className="font-semibold text-sm mb-1"
                  style={{ color: 'var(--color-dark)', fontFamily: 'var(--font-body)' }}
                >
                  {brand.name}
                </p>
                {brand.description && (
                  <p
                    className="text-xs leading-relaxed line-clamp-2"
                    style={{ color: 'var(--color-dark-soft)' }}
                  >
                    {brand.description}
                  </p>
                )}
              </div>
            </Link>
          ))}
        </div>
      </div>
    </section>
  )
}
