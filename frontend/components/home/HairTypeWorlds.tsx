'use client'

import Link from 'next/link'

const hairTypes = [
  {
    label: 'Cabello Seco',
    description: 'Hidratación y nutrición profunda',
    gradient: 'linear-gradient(135deg, #8B6914 0%, #c9a84c 100%)',
    letter: 'S',
    href: '/productos?tipo=seco',
  },
  {
    label: 'Cabello Rizado',
    description: 'Definición y control del rulo',
    gradient: 'linear-gradient(135deg, #5B4B8A 0%, #9B7ED4 100%)',
    letter: 'R',
    href: '/productos?tipo=rizado',
  },
  {
    label: 'Cabello Teñido',
    description: 'Protección del color y brillo',
    gradient: 'linear-gradient(135deg, #C25B56 0%, #E88080 100%)',
    letter: 'T',
    href: '/productos?tipo=tenido',
  },
  {
    label: 'Cabello Graso',
    description: 'Control del sebo y frescura',
    gradient: 'linear-gradient(135deg, #2E7D52 0%, #52C478 100%)',
    letter: 'G',
    href: '/productos?tipo=graso',
  },
  {
    label: 'Cabello Dañado',
    description: 'Reparación intensa y reestructuración',
    gradient: 'linear-gradient(135deg, #1A4F72 0%, #2E86AB 100%)',
    letter: 'D',
    href: '/productos?tipo=danado',
  },
  {
    label: 'Anti-Frizz',
    description: 'Domás el frizz con una fórmula infalible',
    gradient: 'linear-gradient(135deg, #4A2E4A 0%, #8B5A8B 100%)',
    letter: 'F',
    href: '/productos?tipo=frizz',
  },
]

export default function HairTypeWorlds() {
  return (
    <section style={{ background: 'var(--color-cream)', padding: '80px 0' }}>
      <div className="max-w-7xl mx-auto px-6">
        {/* Header */}
        <div className="text-center mb-14">
          <p
            className="text-xs uppercase tracking-widest font-semibold mb-3"
            style={{ color: 'var(--color-primary)' }}
          >
            Encontrá tu solución
          </p>
          <h2 className="section-title">Tu tipo de cabello</h2>
          <div className="decorative-line mt-4 mb-5" />
          <p className="section-subtitle">
            Productos especialmente formulados para cada necesidad capilar
          </p>
        </div>

        {/* Grid */}
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
          {hairTypes.map((type) => (
            <Link
              key={type.label}
              href={type.href}
              className="group flex flex-col items-center gap-4 p-6 transition-all"
              style={{
                background: 'var(--color-white)',
                border: '1px solid var(--color-border)',
              }}
              onMouseEnter={(e) => {
                const el = e.currentTarget
                el.style.borderColor = 'var(--color-primary)'
                el.style.transform = 'translateY(-4px)'
                el.style.boxShadow = '0 12px 30px rgba(51,51,51,0.1)'
              }}
              onMouseLeave={(e) => {
                const el = e.currentTarget
                el.style.borderColor = 'var(--color-border)'
                el.style.transform = 'translateY(0)'
                el.style.boxShadow = 'none'
              }}
            >
              {/* Icon circle */}
              <div
                className="w-14 h-14 rounded-full flex items-center justify-center text-white text-xl font-bold shrink-0"
                style={{ background: type.gradient, fontFamily: 'var(--font-display)', fontStyle: 'italic' }}
              >
                {type.letter}
              </div>

              <div className="text-center">
                <p
                  className="font-semibold text-sm mb-1"
                  style={{ color: 'var(--color-dark)', fontFamily: 'var(--font-body)' }}
                >
                  {type.label}
                </p>
                <p
                  className="text-xs leading-relaxed"
                  style={{ color: 'var(--color-dark-soft)' }}
                >
                  {type.description}
                </p>
              </div>
            </Link>
          ))}
        </div>
      </div>
    </section>
  )
}
