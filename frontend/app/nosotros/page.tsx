import Link from 'next/link'
import { ChevronRight, Award, Users, Heart, Sparkles } from 'lucide-react'

export const metadata = {
  title: 'Nosotros — Tiziano Peluquería',
  description:
    'Conocé la historia de Tiziano: salón de peluquería profesional y distribuidora de productos capilares en Córdoba, Argentina.',
}

const values = [
  {
    Icon: Award,
    title: 'Calidad Profesional',
    text: 'Trabajamos exclusivamente con marcas líderes del mercado profesional. Cada producto que ofrecemos fue probado y aprobado en nuestro salón antes de llegar a tu mano.',
  },
  {
    Icon: Users,
    title: 'Atención Personalizada',
    text: 'Creemos que cada cliente es único. Nuestro equipo asesora de forma individual, ya seas profesional del rubro o busques lo mejor para tu cuidado personal.',
  },
  {
    Icon: Sparkles,
    title: 'Marcas de Primera Línea',
    text: 'Como salón oficial L\'Oréal Professionnel, accedemos a las últimas innovaciones en coloración, tratamientos y styling, y las trasladamos a nuestra distribuidora.',
  },
  {
    Icon: Heart,
    title: 'Compromiso con la Comunidad',
    text: 'Participamos activamente en iniciativas solidarias como "Tijeras Solidarias", llevando cortes gratuitos a familias que lo necesitan en la ciudad de Córdoba.',
  },
]

export default function NosotrosPage() {
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
            <span style={{ color: 'var(--color-primary)' }}>Nosotros</span>
          </nav>
          <h1
            style={{
              fontFamily: 'var(--font-display)',
              fontSize: 'clamp(2rem, 5vw, 3rem)',
              color: 'var(--color-white)',
              fontStyle: 'italic',
            }}
          >
            Nuestra Historia
          </h1>
          <p
            className="mt-2 text-sm max-w-xl"
            style={{ color: 'var(--color-primary-light)', fontStyle: 'italic' }}
          >
            Más que un salón: un espacio donde la pasión por la belleza se transforma en productos y servicios de excelencia.
          </p>
        </div>
      </div>

      {/* Historia */}
      <section style={{ background: 'var(--color-bg)', padding: '64px 0' }}>
        <div className="max-w-7xl mx-auto px-6">
          <div className="grid lg:grid-cols-2 gap-12 items-center">
            <div>
              <p
                className="text-xs uppercase tracking-widest font-semibold mb-4"
                style={{ color: 'var(--color-primary)' }}
              >
                Quiénes somos
              </p>
              <h2
                className="mb-6"
                style={{
                  fontFamily: 'var(--font-display)',
                  fontSize: 'clamp(1.5rem, 3.5vw, 2.25rem)',
                  color: 'var(--color-dark)',
                  lineHeight: 1.15,
                }}
              >
                Tiziano Peluquería &amp; Spa
              </h2>
              <div className="space-y-4 text-sm leading-relaxed" style={{ color: 'var(--color-dark-soft)' }}>
                <p>
                  Nacimos en Córdoba con una idea clara: ofrecer servicios de peluquería de primer nivel y, al mismo tiempo, acercar a profesionales y consumidores los mejores productos capilares del mercado.
                </p>
                <p>
                  Nuestro salón, ubicado en <strong style={{ color: 'var(--color-dark)' }}>Santa Ana 2725</strong>, es un espacio donde conviven la atención personalizada de un salón boutique con la variedad de una distribuidora especializada. Trabajamos con marcas reconocidas internacionalmente y somos salón oficial <strong style={{ color: 'var(--color-dark)' }}>L&apos;Oréal Professionnel</strong>.
                </p>
                <p>
                  Desde nuestro rol de distribuidora, abastecemos a salones y profesionales de toda la provincia con precios mayoristas y minoristas, brindando asesoría técnica en cada venta. Cada producto que vendemos es el mismo que usamos en nuestros servicios de corte, coloración, tratamientos y styling.
                </p>
              </div>
            </div>

            <div
              className="flex items-center justify-center"
              style={{
                aspectRatio: '4/3',
                background: 'linear-gradient(135deg, var(--color-cream) 0%, var(--color-primary-light) 100%)',
                border: '1px solid var(--color-border)',
              }}
            >
              <div className="text-center px-8">
                <span
                  style={{
                    fontFamily: 'var(--font-display)',
                    fontSize: '3rem',
                    fontWeight: 700,
                    fontStyle: 'italic',
                    color: 'var(--color-primary-dark)',
                    display: 'block',
                  }}
                >
                  Tiziano
                </span>
                <span
                  className="text-xs uppercase tracking-[0.3em] font-semibold"
                  style={{ color: 'var(--color-primary)' }}
                >
                  Peluquería &amp; Distribuidora
                </span>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Valores */}
      <section style={{ background: 'var(--color-white)', padding: '64px 0' }}>
        <div className="max-w-7xl mx-auto px-6">
          <p
            className="text-xs uppercase tracking-widest font-semibold mb-3 text-center"
            style={{ color: 'var(--color-primary)' }}
          >
            Nuestros valores
          </p>
          <h2
            className="mb-12 text-center"
            style={{
              fontFamily: 'var(--font-display)',
              fontSize: 'clamp(1.5rem, 3.5vw, 2.25rem)',
              color: 'var(--color-dark)',
              lineHeight: 1.15,
            }}
          >
            Lo que nos define
          </h2>

          <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {values.map(({ Icon, title, text }) => (
              <div
                key={title}
                className="p-6"
                style={{ border: '1px solid var(--color-border)', background: 'var(--color-bg)' }}
              >
                <div
                  className="w-12 h-12 flex items-center justify-center rounded-full mb-5"
                  style={{ background: 'var(--color-cream)' }}
                >
                  <Icon size={22} style={{ color: 'var(--color-primary-dark)' }} />
                </div>
                <h3
                  className="text-sm font-bold uppercase tracking-wide mb-3"
                  style={{ color: 'var(--color-dark)' }}
                >
                  {title}
                </h3>
                <p className="text-sm leading-relaxed" style={{ color: 'var(--color-dark-soft)' }}>
                  {text}
                </p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* CTA */}
      <section
        style={{
          background: 'var(--color-dark)',
          padding: '56px 0',
          borderTop: '3px solid var(--color-primary)',
        }}
      >
        <div className="max-w-7xl mx-auto px-6 text-center">
          <h2
            className="mb-4"
            style={{
              fontFamily: 'var(--font-display)',
              fontSize: 'clamp(1.5rem, 3vw, 2rem)',
              color: 'var(--color-white)',
              fontStyle: 'italic',
            }}
          >
            ¿Querés conocernos?
          </h2>
          <p className="text-sm mb-8 max-w-lg mx-auto" style={{ color: '#aaa' }}>
            Visitá nuestro salón, explorá nuestro catálogo online o escribinos por WhatsApp. Estamos para ayudarte.
          </p>
          <div className="flex flex-wrap justify-center gap-4">
            <Link
              href="/contacto"
              className="inline-block px-6 py-3 text-sm font-semibold uppercase tracking-wider transition-opacity hover:opacity-90"
              style={{ background: 'var(--color-primary)', color: 'var(--color-dark)' }}
            >
              Contacto
            </Link>
            <Link
              href="/productos"
              className="inline-block px-6 py-3 text-sm font-semibold uppercase tracking-wider transition-opacity hover:opacity-90"
              style={{ border: '1px solid var(--color-primary)', color: 'var(--color-primary)' }}
            >
              Ver productos
            </Link>
          </div>
        </div>
      </section>
    </>
  )
}
