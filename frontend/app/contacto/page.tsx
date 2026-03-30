import Link from 'next/link'
import { ChevronRight, MapPin, Phone, Mail, Clock, MessageCircle } from 'lucide-react'

export const metadata = {
  title: 'Contacto — Tiziano Peluquería',
  description:
    'Encontranos en Santa Ana 2725, Córdoba. Escribinos por WhatsApp o visitá nuestro salón.',
}

function IconInstagram({ size = 18 }: { size?: number }) {
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <rect x="2" y="2" width="20" height="20" rx="5" ry="5" />
      <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
      <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" />
    </svg>
  )
}

function IconFacebook({ size = 18 }: { size?: number }) {
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
    </svg>
  )
}

const contactInfo = [
  {
    Icon: MapPin,
    label: 'Dirección',
    value: 'Santa Ana 2725, Loc. 2, Córdoba',
    href: 'https://www.google.com/maps/search/?api=1&query=Santa+Ana+2725+Cordoba+Argentina',
  },
  {
    Icon: Phone,
    label: 'Teléfono',
    value: '(0351) 619-7836',
    href: 'tel:+5493516197836',
  },
  {
    Icon: Mail,
    label: 'Email',
    value: 'tizianopeluqueriaspa@gmail.com',
    href: 'mailto:tizianopeluqueriaspa@gmail.com',
  },
  {
    Icon: Clock,
    label: 'Horarios',
    value: 'Lunes a Sábado de 9:00 a 19:00 hs',
    href: null,
  },
]

export default function ContactoPage() {
  return (
    <>
      {/* Hero */}
      <div
        style={{
          background: 'var(--color-dark)',
          padding: '56px 0 48px',
          borderBottom: '3px solid var(--color-primary)',
        }}
      >
        <div className="max-w-7xl mx-auto px-6">
          <nav className="mb-4 flex items-center gap-2 text-xs" style={{ color: '#888' }}>
            <Link href="/" style={{ color: '#888' }}>Inicio</Link>
            <ChevronRight size={12} />
            <span style={{ color: 'var(--color-primary)' }}>Contacto</span>
          </nav>
          <h1
            style={{
              fontFamily: 'var(--font-display)',
              fontSize: 'clamp(2rem, 5vw, 3rem)',
              color: 'var(--color-white)',
              fontStyle: 'italic',
            }}
          >
            Contacto
          </h1>
          <p
            className="mt-2 text-sm max-w-xl"
            style={{ color: 'var(--color-primary-light)', fontStyle: 'italic' }}
          >
            Estamos para asesorarte. Visitá nuestro salón, llamanos o escribinos por WhatsApp.
          </p>
        </div>
      </div>

      {/* Content */}
      <section style={{ background: 'var(--color-bg)', padding: '64px 0 80px' }}>
        <div className="max-w-7xl mx-auto px-6">
          <div className="grid lg:grid-cols-2 gap-12">
            {/* Left: info */}
            <div>
              <p
                className="text-xs uppercase tracking-widest font-semibold mb-4"
                style={{ color: 'var(--color-primary)' }}
              >
                Nuestros datos
              </p>
              <h2
                className="mb-8"
                style={{
                  fontFamily: 'var(--font-display)',
                  fontSize: 'clamp(1.5rem, 3vw, 2rem)',
                  color: 'var(--color-dark)',
                  lineHeight: 1.15,
                }}
              >
                Tiziano Peluquería &amp; Spa
              </h2>

              <ul className="space-y-5 mb-10">
                {contactInfo.map(({ Icon, label, value, href }) => (
                  <li key={label} className="flex gap-4 items-start">
                    <div
                      className="w-10 h-10 flex items-center justify-center rounded-full shrink-0"
                      style={{ background: 'var(--color-cream)' }}
                    >
                      <Icon size={18} style={{ color: 'var(--color-primary-dark)' }} />
                    </div>
                    <div>
                      <p className="text-xs font-semibold uppercase tracking-wider mb-0.5" style={{ color: 'var(--color-dark-soft)' }}>
                        {label}
                      </p>
                      {href ? (
                        <a
                          href={href}
                          target={href.startsWith('http') ? '_blank' : undefined}
                          rel={href.startsWith('http') ? 'noopener noreferrer' : undefined}
                          className="text-sm font-semibold hover:opacity-70 transition-opacity"
                          style={{ color: 'var(--color-dark)' }}
                        >
                          {value}
                        </a>
                      ) : (
                        <p className="text-sm font-semibold" style={{ color: 'var(--color-dark)' }}>{value}</p>
                      )}
                    </div>
                  </li>
                ))}
              </ul>

              {/* WhatsApp CTA */}
              <a
                href="https://wa.me/5493516197836"
                target="_blank"
                rel="noopener noreferrer"
                className="inline-flex items-center gap-3 px-6 py-3.5 text-sm font-semibold uppercase tracking-wider transition-opacity hover:opacity-90"
                style={{ background: '#25D366', color: '#fff', borderRadius: '4px' }}
              >
                <MessageCircle size={20} />
                Escribinos por WhatsApp
              </a>

              {/* Redes */}
              <div className="mt-8 flex gap-4">
                <a
                  href="https://instagram.com/tiziano_peluqueria.spa"
                  target="_blank"
                  rel="noopener noreferrer"
                  className="w-10 h-10 flex items-center justify-center rounded-full transition-all"
                  style={{ border: '1px solid var(--color-border)', color: 'var(--color-dark-soft)' }}
                  aria-label="Instagram"
                >
                  <IconInstagram size={18} />
                </a>
                <a
                  href="https://facebook.com/tiziano.peluqueria.spa"
                  target="_blank"
                  rel="noopener noreferrer"
                  className="w-10 h-10 flex items-center justify-center rounded-full transition-all"
                  style={{ border: '1px solid var(--color-border)', color: 'var(--color-dark-soft)' }}
                  aria-label="Facebook"
                >
                  <IconFacebook size={18} />
                </a>
              </div>
            </div>

            {/* Right: map */}
            <div>
              <div
                className="overflow-hidden"
                style={{ border: '1px solid var(--color-border)', background: 'var(--color-white)' }}
              >
                <iframe
                  title="Ubicación de Tiziano Peluquería & Spa"
                  src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1702.5148749724533!2d-64.21958032922208!3d-31.413306410447223!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x943298a22f81224f%3A0x2b8aaf641127173b!2sTiziano%20Peluqueria%20%26%20Spa!5e0!3m2!1ses-419!2sar!4v1774835127705!5m2!1ses-419!2sar"
                  className="w-full"
                  style={{ height: '450px', border: 0 }}
                  allowFullScreen
                  loading="lazy"
                  referrerPolicy="no-referrer-when-downgrade"
                />
              </div>

              {/* Formulario simple */}
              <div className="mt-8 p-6" style={{ border: '1px solid var(--color-border)', background: 'var(--color-white)' }}>
                <h3
                  className="text-sm font-bold uppercase tracking-wide mb-5"
                  style={{ color: 'var(--color-dark)' }}
                >
                  Envianos un mensaje
                </h3>
                <form
                  action="mailto:tizianopeluqueriaspa@gmail.com"
                  method="POST"
                  encType="text/plain"
                  className="space-y-4"
                >
                  <div>
                    <label htmlFor="name" className="block text-xs font-semibold uppercase tracking-wider mb-1.5" style={{ color: 'var(--color-dark-soft)' }}>
                      Nombre
                    </label>
                    <input
                      id="name"
                      name="name"
                      type="text"
                      required
                      className="w-full px-4 py-2.5 text-sm outline-none"
                      style={{
                        border: '1px solid var(--color-border)',
                        background: 'var(--color-bg)',
                        color: 'var(--color-dark)',
                      }}
                    />
                  </div>
                  <div>
                    <label htmlFor="email" className="block text-xs font-semibold uppercase tracking-wider mb-1.5" style={{ color: 'var(--color-dark-soft)' }}>
                      Email
                    </label>
                    <input
                      id="email"
                      name="email"
                      type="email"
                      required
                      className="w-full px-4 py-2.5 text-sm outline-none"
                      style={{
                        border: '1px solid var(--color-border)',
                        background: 'var(--color-bg)',
                        color: 'var(--color-dark)',
                      }}
                    />
                  </div>
                  <div>
                    <label htmlFor="message" className="block text-xs font-semibold uppercase tracking-wider mb-1.5" style={{ color: 'var(--color-dark-soft)' }}>
                      Mensaje
                    </label>
                    <textarea
                      id="message"
                      name="message"
                      rows={4}
                      required
                      className="w-full px-4 py-2.5 text-sm outline-none resize-y"
                      style={{
                        border: '1px solid var(--color-border)',
                        background: 'var(--color-bg)',
                        color: 'var(--color-dark)',
                      }}
                    />
                  </div>
                  <button
                    type="submit"
                    className="w-full px-6 py-3 text-sm font-semibold uppercase tracking-wider transition-opacity hover:opacity-90"
                    style={{ background: 'var(--color-dark)', color: 'var(--color-white)' }}
                  >
                    Enviar mensaje
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </section>
    </>
  )
}
