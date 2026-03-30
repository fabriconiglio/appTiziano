'use client'

import Link from 'next/link'
import { MapPin, Phone, Mail } from 'lucide-react'

function IconInstagram({ size = 16 }: { size?: number }) {
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <rect x="2" y="2" width="20" height="20" rx="5" ry="5" />
      <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
      <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" />
    </svg>
  )
}

function IconFacebook({ size = 16 }: { size?: number }) {
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
    </svg>
  )
}

function IconYoutube({ size = 16 }: { size?: number }) {
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M22.54 6.42a2.78 2.78 0 0 0-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46A2.78 2.78 0 0 0 1.46 6.42 29 29 0 0 0 1 12a29 29 0 0 0 .46 5.58 2.78 2.78 0 0 0 1.95 1.95C5.12 20 12 20 12 20s6.88 0 8.59-.47a2.78 2.78 0 0 0 1.95-1.95A29 29 0 0 0 23 12a29 29 0 0 0-.46-5.58z" />
      <polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02" />
    </svg>
  )
}

export default function Footer() {
  return (
    <footer style={{ background: 'var(--color-dark)', color: '#ccc' }}>
      {/* Upper footer */}
      <div className="max-w-7xl mx-auto px-6 py-14 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
        {/* Brand */}
        <div>
          <div className="mb-5">
            <span
              style={{
                fontFamily: 'var(--font-display)',
                color: 'var(--color-primary)',
                fontSize: '1.8rem',
                fontWeight: 700,
                fontStyle: 'italic',
                display: 'block',
              }}
            >
              Tiziano
            </span>
            <span
              style={{
                color: 'var(--color-primary-light)',
                fontSize: '0.6rem',
                letterSpacing: '0.3em',
                textTransform: 'uppercase',
                fontWeight: 600,
              }}
            >
              Peluquería Profesional
            </span>
          </div>
          <p className="text-sm leading-relaxed" style={{ color: '#aaa' }}>
            Productos de belleza y cuidado capilar para profesionales y salones de peluquería. Calidad premium que se siente.
          </p>
          <div className="flex gap-4 mt-6">
            {[
              { Icon: IconInstagram, href: '#' },
              { Icon: IconFacebook, href: '#' },
              { Icon: IconYoutube, href: '#' },
            ].map(({ Icon, href }, i) => (
              <a
                key={i}
                href={href}
                className="w-9 h-9 flex items-center justify-center rounded-full transition-all"
                style={{ border: '1px solid #555', color: '#aaa' }}
                onMouseEnter={(e) => {
                  const el = e.currentTarget as HTMLAnchorElement
                  el.style.borderColor = 'var(--color-primary)'
                  el.style.color = 'var(--color-primary)'
                }}
                onMouseLeave={(e) => {
                  const el = e.currentTarget as HTMLAnchorElement
                  el.style.borderColor = '#555'
                  el.style.color = '#aaa'
                }}
              >
                <Icon size={16} />
              </a>
            ))}
          </div>
        </div>

        {/* Categorías */}
        <div>
          <h4
            className="mb-5 text-xs uppercase tracking-widest font-semibold"
            style={{ color: 'var(--color-primary)' }}
          >
            Categorías
          </h4>
          <ul className="space-y-2.5 text-sm">
            {['Shampoo', 'Acondicionador', 'Máscaras', 'Tratamientos', 'Coloración', 'Styling'].map(
              (cat) => (
                <li key={cat}>
                  <Link
                    href="/productos"
                    className="hover:text-white transition-colors"
                    style={{ color: '#aaa' }}
                  >
                    {cat}
                  </Link>
                </li>
              )
            )}
          </ul>
        </div>

        {/* Información */}
        <div>
          <h4
            className="mb-5 text-xs uppercase tracking-widest font-semibold"
            style={{ color: 'var(--color-primary)' }}
          >
            Información
          </h4>
          <ul className="space-y-2.5 text-sm">
            {[
              { label: 'Nosotros', href: '/nosotros' },
              { label: 'Productos', href: '/productos' },
              { label: 'Contacto', href: '/contacto' },
              { label: 'Preguntas frecuentes', href: '/faq' },
              { label: 'Política de envíos', href: '/envios' },
            ].map(({ label, href }) => (
              <li key={label}>
                <Link href={href} className="hover:text-white transition-colors" style={{ color: '#aaa' }}>
                  {label}
                </Link>
              </li>
            ))}
          </ul>
        </div>

        {/* Contacto */}
        <div>
          <h4
            className="mb-5 text-xs uppercase tracking-widest font-semibold"
            style={{ color: 'var(--color-primary)' }}
          >
            Contacto
          </h4>
          <ul className="space-y-4 text-sm">
            <li className="flex gap-3 items-start" style={{ color: '#aaa' }}>
              <MapPin size={16} className="mt-0.5 shrink-0" style={{ color: 'var(--color-primary)' }} />
              <span>Buenos Aires, Argentina</span>
            </li>
            <li className="flex gap-3 items-center" style={{ color: '#aaa' }}>
              <Phone size={16} className="shrink-0" style={{ color: 'var(--color-primary)' }} />
              <span>(+5411) 4116-0413</span>
            </li>
            <li className="flex gap-3 items-center" style={{ color: '#aaa' }}>
              <Mail size={16} className="shrink-0" style={{ color: 'var(--color-primary)' }} />
              <span>info@tiziano.com.ar</span>
            </li>
          </ul>
        </div>
      </div>

      {/* Bottom bar */}
      <div
        className="border-t px-6 py-5"
        style={{ borderColor: '#444' }}
      >
        <div className="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-3 text-xs" style={{ color: '#666' }}>
          <span>© {new Date().getFullYear()} Tiziano Peluquería. Todos los derechos reservados.</span>
          <div className="flex gap-6">
            <Link href="/legales" className="hover:text-white transition-colors">Legales</Link>
            <Link href="/privacidad" className="hover:text-white transition-colors">Privacidad</Link>
          </div>
        </div>
      </div>
    </footer>
  )
}
