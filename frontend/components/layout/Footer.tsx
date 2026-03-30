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

function IconWhatsApp({ size = 16 }: { size?: number }) {
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill="currentColor">
      <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
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
            Salón de peluquería profesional y distribuidora de productos capilares en Córdoba. Calidad premium para profesionales y consumidores.
          </p>
          <div className="flex gap-4 mt-6">
            {[
              { Icon: IconInstagram, href: 'https://instagram.com/tiziano_peluqueria.spa', label: 'Instagram' },
              { Icon: IconFacebook, href: 'https://facebook.com/tiziano.peluqueria.spa', label: 'Facebook' },
              { Icon: IconWhatsApp, href: 'https://wa.me/5493516197836', label: 'WhatsApp' },
            ].map(({ Icon, href, label }) => (
              <a
                key={label}
                href={href}
                target="_blank"
                rel="noopener noreferrer"
                aria-label={label}
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
              <span>Santa Ana 2725, Loc. 2, Córdoba</span>
            </li>
            <li className="flex gap-3 items-center" style={{ color: '#aaa' }}>
              <Phone size={16} className="shrink-0" style={{ color: 'var(--color-primary)' }} />
              <span>(0351) 619-7836</span>
            </li>
            <li className="flex gap-3 items-center" style={{ color: '#aaa' }}>
              <Mail size={16} className="shrink-0" style={{ color: 'var(--color-primary)' }} />
              <span>tizianopeluqueriaspa@gmail.com</span>
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
