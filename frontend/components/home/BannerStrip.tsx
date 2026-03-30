import Link from 'next/link'
import { ArrowRight } from 'lucide-react'

const banners = [
  {
    tag: 'Línea Exclusiva',
    title: 'Tratamiento\nArgán Premium',
    sub: 'El oro líquido del Marruecos para tu cabello',
    href: '/productos',
    bg: 'linear-gradient(135deg, #3d2b1a 0%, #6b4c35 100%)',
    accent: '#c9bc9d',
  },
  {
    tag: 'Anti-Age Capilar',
    title: 'Mundo\nCaviar',
    sub: 'Los secretos de las profundidades del mar para tu cabello',
    href: '/productos',
    bg: 'linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%)',
    accent: '#e0d8c8',
  },
]

export default function BannerStrip() {
  return (
    <section style={{ padding: '0 0 80px 0', background: 'var(--color-white)' }}>
      <div className="max-w-7xl mx-auto px-6 grid md:grid-cols-2 gap-5">
        {banners.map((b) => (
          <Link
            key={b.title}
            href={b.href}
            className="group relative overflow-hidden flex flex-col justify-end p-8"
            style={{
              background: b.bg,
              minHeight: '260px',
            }}
          >
            {/* Pattern */}
            <div
              className="absolute inset-0 opacity-10 group-hover:opacity-15 transition-opacity"
              style={{
                backgroundImage: `radial-gradient(circle at 70% 20%, ${b.accent}, transparent 60%)`,
              }}
            />

            {/* Vertical line */}
            <div
              className="absolute left-0 top-8 bottom-8 w-0.5"
              style={{ background: b.accent, opacity: 0.5 }}
            />

            <div className="relative z-10">
              <span
                className="text-xs uppercase tracking-widest font-semibold mb-3 block"
                style={{ color: b.accent }}
              >
                {b.tag}
              </span>
              <h3
                className="font-bold mb-2 whitespace-pre-line"
                style={{
                  fontFamily: 'var(--font-display)',
                  fontSize: 'clamp(1.5rem, 3vw, 2rem)',
                  color: '#fff',
                  lineHeight: 1.1,
                }}
              >
                {b.title}
              </h3>
              <p className="text-sm mb-5" style={{ color: 'rgba(255,255,255,0.7)' }}>
                {b.sub}
              </p>
              <span
                className="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest transition-all group-hover:gap-3"
                style={{ color: b.accent, borderBottom: `1px solid ${b.accent}`, paddingBottom: '2px' }}
              >
                Ver más <ArrowRight size={13} />
              </span>
            </div>
          </Link>
        ))}
      </div>
    </section>
  )
}
