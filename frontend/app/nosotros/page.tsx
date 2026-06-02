import Link from 'next/link'
import { ChevronRight, Sparkles, Truck, ShoppingBag, Handshake } from 'lucide-react'

export const metadata = {
  title: 'Nosotros — Tiziano Peluquería',
  description:
    'Conocé la historia de Tiziano: salón de peluquería profesional y distribuidora de productos capilares en Córdoba, Argentina.',
}

const values = [
  {
    Icon: Sparkles,
    title: 'Asesoramiento Profesional',
    text: 'Te ayudamos a elegir los productos ideales para cada necesidad. Brindamos atención personalizada tanto a profesionales como a clientes finales.',
  },
  {
    Icon: Truck,
    title: 'Distribución Rápida y Segura',
    text: 'Contamos con envíos a todo el país y un proceso ágil para que recibas tus productos en tiempo y forma.',
  },
  {
    Icon: ShoppingBag,
    title: 'Amplio Catálogo de Productos',
    text: 'Trabajamos con las mejores marcas del mercado en coloración, tratamientos, styling, herramientas y cuidado capilar.',
  },
  {
    Icon: Handshake,
    title: 'Atención para Profesionales',
    text: 'Acompañamos el crecimiento de peluquerías y salones con asesoramiento, stock permanente y precios competitivos.',
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
                Tiziano Artículos de Peluquería
              </h2>
              <div className="space-y-4 text-sm leading-relaxed" style={{ color: 'var(--color-dark-soft)' }}>
                <p>
                  Nacimos en Córdoba con una idea clara:
                </p>
                <p>
                  Somos una distribuidora de productos capilares dedicada a acompañar el trabajo de profesionales de la peluquería con insumos de calidad y confianza. Nos especializamos en ofrecer una amplia variedad de marcas y productos que se adaptan a las necesidades del día a día en el salón.
                </p>
                <p>
                  Trabajamos con ventas por mayor y menor, abasteciendo tanto a peluqueros como a perfumerías, brindando siempre atención cercana, asesoramiento y compromiso en cada venta.
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
