import Link from 'next/link'
import { ChevronRight, MapPin, Truck, Package, Clock, ShieldCheck, AlertTriangle } from 'lucide-react'

export const metadata = {
  title: 'Métodos de Envío — Tiziano Peluquería',
  description:
    'Conocé nuestras opciones de envío: retiro en local gratis, envío en Córdoba en 24-48hs, y envíos a todo el país.',
}

const shippingMethods = [
  {
    Icon: MapPin,
    title: 'Retiro en local',
    cost: 'Gratis',
    time: 'Inmediato (una vez confirmado el pago)',
    description:
      'Retirá tu pedido sin costo en nuestro local. Te avisamos por email cuando esté listo para retirar.',
    details: [
      'Dirección: Santa Ana 2725, Loc. 2, Córdoba',
      'Horario de retiro: Lunes a Viernes de 9:00 a 18:00, Sábados de 9:00 a 13:00',
      'Presentar DNI del titular de la compra',
      'Plazo para retiro: 7 días hábiles desde la confirmación',
    ],
  },
  {
    Icon: Truck,
    title: 'Envío a Córdoba Capital y alrededores',
    cost: 'Consultá el costo al finalizar la compra',
    time: '24 a 48 horas hábiles',
    description:
      'Entrega rápida en Córdoba Capital y localidades cercanas. Ideal para recibir tus productos profesionales sin espera.',
    details: [
      'Cobertura: Córdoba Capital, Villa Allende, Mendiolaza, Unquillo, La Calera, Río Ceballos, Alta Gracia',
      'Envío por Uber Motos',
      'Horario de entrega: de 9:00 a 18:00 hs',
      'Te contactamos para coordinar la entrega',
    ],
  },
  {
    Icon: Package,
    title: 'Envío al interior del país',
    cost: 'Según destino y peso (calculado al checkout)',
    time: '3 a 5 días hábiles',
    description:
      'Llegamos a todo el territorio argentino con Andreani. El costo se calcula automáticamente al checkout según peso y destino.',
    details: [
      'Cobertura: todo el territorio nacional',
      'Servicio: Andreani',
      'Código de seguimiento enviado por email',
      'Los plazos comienzan a partir de la confirmación del pago',
    ],
  },
]

const importantInfo = [
  {
    Icon: ShieldCheck,
    title: 'Empaque seguro',
    text: 'Todos los productos son embalados con materiales de protección para evitar daños durante el transporte. Los productos frágiles llevan empaque reforzado.',
  },
  {
    Icon: Clock,
    title: 'Plazos estimados',
    text: 'Los plazos de entrega son estimados y pueden variar según disponibilidad del producto, condiciones climáticas o demora del servicio de correo.',
  },
  {
    Icon: AlertTriangle,
    title: 'Verificá tu pedido',
    text: 'Al recibir tu pedido, verificá que el paquete esté en buenas condiciones. Si detectás algún daño, informá al transportista y contactanos inmediatamente.',
  },
]

export default function EnviosPage() {
  return (
    <>
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
            <span style={{ color: 'var(--color-primary)' }}>Métodos de Envío</span>
          </nav>
          <h1
            style={{
              fontFamily: 'var(--font-display)',
              fontSize: 'clamp(2rem, 5vw, 3rem)',
              color: 'var(--color-white)',
              fontStyle: 'italic',
            }}
          >
            Métodos de Envío
          </h1>
          <p
            className="mt-2 text-sm max-w-xl"
            style={{ color: 'var(--color-primary-light)', fontStyle: 'italic' }}
          >
            Retirá en nuestro local o recibí tus productos en cualquier punto del país.
          </p>
        </div>
      </div>

      <section style={{ background: 'var(--color-bg)', padding: '64px 0' }}>
        <div className="max-w-4xl mx-auto px-6">
          <div className="flex flex-col gap-6">
            {shippingMethods.map((method) => (
              <div
                key={method.title}
                style={{
                  background: 'var(--color-white)',
                  border: '1px solid var(--color-border)',
                }}
              >
                <div className="p-6">
                  <div className="flex items-start gap-4 mb-4">
                    <div
                      className="w-12 h-12 flex items-center justify-center rounded-full shrink-0"
                      style={{ background: 'var(--color-cream)' }}
                    >
                      <method.Icon size={22} style={{ color: 'var(--color-primary-dark)' }} />
                    </div>
                    <div className="flex-1">
                      <h2
                        className="text-base font-bold uppercase tracking-wide mb-1"
                        style={{ color: 'var(--color-dark)' }}
                      >
                        {method.title}
                      </h2>
                      <p className="text-sm" style={{ color: 'var(--color-dark-soft)' }}>
                        {method.description}
                      </p>
                    </div>
                  </div>

                  <div
                    className="grid sm:grid-cols-2 gap-4 mb-5 p-4"
                    style={{ background: 'var(--color-bg)', border: '1px solid var(--color-border)' }}
                  >
                    <div>
                      <p
                        className="text-xs uppercase tracking-wider font-semibold mb-1"
                        style={{ color: 'var(--color-dark-soft)' }}
                      >
                        Costo
                      </p>
                      <p className="text-sm font-semibold" style={{ color: 'var(--color-dark)' }}>
                        {method.cost}
                      </p>
                    </div>
                    <div>
                      <p
                        className="text-xs uppercase tracking-wider font-semibold mb-1"
                        style={{ color: 'var(--color-dark-soft)' }}
                      >
                        Tiempo estimado
                      </p>
                      <p className="text-sm font-semibold" style={{ color: 'var(--color-dark)' }}>
                        {method.time}
                      </p>
                    </div>
                  </div>

                  <ul className="flex flex-col gap-2">
                    {method.details.map((detail) => (
                      <li key={detail} className="flex items-start gap-2 text-sm" style={{ color: 'var(--color-dark-soft)' }}>
                        <ChevronRight size={14} className="mt-0.5 shrink-0" style={{ color: 'var(--color-primary)' }} />
                        <span>{detail}</span>
                      </li>
                    ))}
                  </ul>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section style={{ background: 'var(--color-white)', padding: '64px 0' }}>
        <div className="max-w-4xl mx-auto px-6">
          <p
            className="text-xs uppercase tracking-widest font-semibold mb-3 text-center"
            style={{ color: 'var(--color-primary)' }}
          >
            Información importante
          </p>
          <h2
            className="mb-10 text-center"
            style={{
              fontFamily: 'var(--font-display)',
              fontSize: 'clamp(1.5rem, 3.5vw, 2.25rem)',
              color: 'var(--color-dark)',
              lineHeight: 1.15,
            }}
          >
            Antes de realizar tu compra
          </h2>
          <div className="grid sm:grid-cols-3 gap-6">
            {importantInfo.map((info) => (
              <div
                key={info.title}
                className="p-6"
                style={{ border: '1px solid var(--color-border)', background: 'var(--color-bg)' }}
              >
                <div
                  className="w-10 h-10 flex items-center justify-center rounded-full mb-4"
                  style={{ background: 'var(--color-cream)' }}
                >
                  <info.Icon size={18} style={{ color: 'var(--color-primary-dark)' }} />
                </div>
                <h3
                  className="text-sm font-bold uppercase tracking-wide mb-2"
                  style={{ color: 'var(--color-dark)' }}
                >
                  {info.title}
                </h3>
                <p className="text-sm leading-relaxed" style={{ color: 'var(--color-dark-soft)' }}>
                  {info.text}
                </p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section
        style={{
          background: 'var(--color-dark)',
          padding: '48px 0',
          borderTop: '3px solid var(--color-primary)',
        }}
      >
        <div className="max-w-7xl mx-auto px-6 text-center">
          <h2
            className="mb-3"
            style={{
              fontFamily: 'var(--font-display)',
              fontSize: 'clamp(1.3rem, 3vw, 1.8rem)',
              color: 'var(--color-white)',
              fontStyle: 'italic',
            }}
          >
            ¿Tenés dudas sobre tu envío?
          </h2>
          <p className="text-sm mb-6 max-w-lg mx-auto" style={{ color: '#aaa' }}>
            Escribinos por WhatsApp y te ayudamos con el seguimiento de tu pedido o cualquier consulta.
          </p>
          <div className="flex flex-wrap justify-center gap-4">
            <a
              href="https://wa.me/5493518586698"
              target="_blank"
              rel="noopener noreferrer"
              className="inline-block px-6 py-3 text-sm font-semibold uppercase tracking-wider transition-opacity hover:opacity-90"
              style={{ background: 'var(--color-primary)', color: 'var(--color-dark)' }}
            >
              WhatsApp
            </a>
            <Link
              href="/faq"
              className="inline-block px-6 py-3 text-sm font-semibold uppercase tracking-wider transition-opacity hover:opacity-90"
              style={{ border: '1px solid var(--color-primary)', color: 'var(--color-primary)' }}
            >
              Preguntas frecuentes
            </Link>
          </div>
        </div>
      </section>
    </>
  )
}
