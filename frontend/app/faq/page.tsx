'use client'

import { useState } from 'react'
import Link from 'next/link'
import { ChevronRight, ChevronDown, ShoppingCart, Truck, RotateCcw, Package, UserCircle } from 'lucide-react'

interface FAQ {
  question: string
  answer: string
}

interface FAQSection {
  title: string
  icon: React.ElementType
  items: FAQ[]
}

const faqSections: FAQSection[] = [
  {
    title: 'Pedidos y compras',
    icon: ShoppingCart,
    items: [
      {
        question: '¿Cómo hago una compra?',
        answer:
          'Navegá por nuestro catálogo, agregá los productos al carrito y hacé clic en "Proceder al checkout". Vas a necesitar crear una cuenta o iniciar sesión para completar la compra.',
      },
      {
        question: '¿Qué métodos de pago aceptan?',
        answer:
          'Aceptamos tarjetas de crédito y débito (Visa, Mastercard, American Express) a través de Taca Taca, y también transferencia bancaria. Los datos bancarios se muestran al momento de confirmar el pedido.',
      },
      {
        question: '¿Cuánto tarda en confirmarse mi pedido?',
        answer:
          'Si pagás con tarjeta a través de Taca Taca, la confirmación es inmediata. Si elegís transferencia bancaria, confirmamos tu pedido una vez que recibamos y verifiquemos el comprobante de pago (generalmente en minutos durante el horario comercial).',
      },
      {
        question: '¿Puedo modificar o cancelar mi pedido?',
        answer:
          'Si tu pedido aún no fue despachado, contactanos por WhatsApp o email y haremos lo posible por modificarlo. También podés ejercer tu derecho de arrepentimiento dentro de los 10 días corridos.',
      },
    ],
  },
  {
    title: 'Envíos',
    icon: Truck,
    items: [
      {
        question: '¿Hacen envíos a todo el país?',
        answer:
          'Sí, realizamos envíos a todo el territorio argentino. También podés retirar tu pedido gratis en nuestro local de Santa Ana 2725, Córdoba.',
      },
      {
        question: '¿Cuánto cuesta el envío?',
        answer:
          'El costo varía según tu ubicación y el peso del paquete. Para Córdoba Capital y alrededores el envío es más económico. Podés consultar los detalles en nuestra página de Métodos de Envío.',
      },
      {
        question: '¿Cuánto tarda en llegar mi pedido?',
        answer:
          'Para Córdoba Capital y alrededores: 24 a 48 horas hábiles. Para el resto del país: 3 a 5 días hábiles, dependiendo de la localidad. Los plazos comienzan a partir de la confirmación del pago.',
      },
      {
        question: '¿Puedo hacer seguimiento de mi envío?',
        answer:
          'Sí, una vez que tu pedido sea despachado te enviaremos un email con el código de seguimiento para que puedas rastrearlo.',
      },
    ],
  },
  {
    title: 'Devoluciones y arrepentimiento',
    icon: RotateCcw,
    items: [
      {
        question: '¿Puedo devolver un producto?',
        answer:
          'Sí, de acuerdo con el Art. 34 de la Ley 24.240 de Defensa del Consumidor, tenés derecho a devolver el producto dentro de los 10 días corridos desde la recepción, sin necesidad de indicar motivo.',
      },
      {
        question: '¿Cómo solicito el arrepentimiento?',
        answer:
          'Usá el Botón de Arrepentimiento que se encuentra en el pie de página de nuestro sitio. Completá el formulario con tu nombre, email y número de pedido. No necesitás estar registrado para hacerlo.',
      },
      {
        question: '¿Cuándo recibo el reembolso?',
        answer:
          'Una vez recibida y verificada la devolución, procesamos el reembolso dentro de los 10 días hábiles. El medio de reembolso será el mismo utilizado para el pago original.',
      },
      {
        question: '¿Hay productos excluidos de devolución?',
        answer:
          'Por cuestiones de higiene y seguridad, los productos que fueron abiertos o usados no pueden ser devueltos, salvo que presenten defectos de fabricación.',
      },
    ],
  },
  {
    title: 'Productos',
    icon: Package,
    items: [
      {
        question: '¿Los productos son originales?',
        answer:
          'Absolutamente. Somos distribuidores autorizados de todas las marcas que comercializamos. Todos los productos provienen directamente de los fabricantes o distribuidores oficiales.',
      },
      {
        question: '¿Cómo conservo los productos?',
        answer:
          'Recomendamos almacenar los productos en un lugar fresco y seco, alejado de la luz directa del sol. Cada producto incluye indicaciones específicas de conservación en su envase.',
      },
      {
        question: '¿Me pueden asesorar sobre qué producto necesito?',
        answer:
          'Por supuesto. Escribinos por WhatsApp al (0351) 619-7836 contándonos tu tipo de cabello y necesidad, y nuestro equipo te va a recomendar los productos ideales.',
      },
    ],
  },
  {
    title: 'Cuenta y seguridad',
    icon: UserCircle,
    items: [
      {
        question: '¿Necesito una cuenta para comprar?',
        answer:
          'Sí, necesitás registrarte para realizar una compra. Podés crear tu cuenta con email y contraseña o iniciar sesión con Google. El registro es rápido y gratuito.',
      },
      {
        question: '¿Cómo recupero mi contraseña?',
        answer:
          'En la página de inicio de sesión, hacé clic en "¿Olvidaste tu contraseña?" e ingresá tu email. Te enviaremos un enlace para restablecerla.',
      },
      {
        question: '¿Mis datos personales están protegidos?',
        answer:
          'Sí, cumplimos con la Ley 25.326 de Protección de Datos Personales. Tu información se almacena de forma segura y nunca se comparte con terceros sin tu consentimiento.',
      },
    ],
  },
]

function AccordionItem({ question, answer }: FAQ) {
  const [open, setOpen] = useState(false)

  return (
    <div style={{ borderBottom: '1px solid var(--color-border)' }}>
      <button
        onClick={() => setOpen(!open)}
        className="w-full flex items-center justify-between py-4 px-1 text-left gap-4"
      >
        <span
          className="text-sm font-medium"
          style={{ color: 'var(--color-dark)' }}
        >
          {question}
        </span>
        <ChevronDown
          size={16}
          className="shrink-0 transition-transform duration-200"
          style={{
            color: 'var(--color-dark-soft)',
            transform: open ? 'rotate(180deg)' : 'rotate(0deg)',
          }}
        />
      </button>
      {open && (
        <div className="pb-4 px-1">
          <p className="text-sm leading-relaxed" style={{ color: 'var(--color-dark-soft)' }}>
            {answer}
          </p>
        </div>
      )}
    </div>
  )
}

export default function FAQPage() {
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
            <span style={{ color: 'var(--color-primary)' }}>Preguntas Frecuentes</span>
          </nav>
          <h1
            style={{
              fontFamily: 'var(--font-display)',
              fontSize: 'clamp(2rem, 5vw, 3rem)',
              color: 'var(--color-white)',
              fontStyle: 'italic',
            }}
          >
            Preguntas Frecuentes
          </h1>
          <p
            className="mt-2 text-sm max-w-xl"
            style={{ color: 'var(--color-primary-light)', fontStyle: 'italic' }}
          >
            Encontrá respuestas a las consultas más comunes sobre compras, envíos, devoluciones y más.
          </p>
        </div>
      </div>

      <section style={{ background: 'var(--color-bg)', padding: '64px 0' }}>
        <div className="max-w-3xl mx-auto px-6">
          <div className="flex flex-col gap-10">
            {faqSections.map((section) => {
              const SectionIcon = section.icon
              return (
                <div key={section.title}>
                  <div className="flex items-center gap-3 mb-4">
                    <div
                      className="w-10 h-10 flex items-center justify-center rounded-full"
                      style={{ background: 'var(--color-cream)' }}
                    >
                      <SectionIcon size={18} style={{ color: 'var(--color-primary-dark)' }} />
                    </div>
                    <h2
                      className="text-base font-bold uppercase tracking-wide"
                      style={{ color: 'var(--color-dark)' }}
                    >
                      {section.title}
                    </h2>
                  </div>
                  <div
                    className="pl-0"
                    style={{
                      background: 'var(--color-white)',
                      border: '1px solid var(--color-border)',
                      padding: '0 20px',
                    }}
                  >
                    {section.items.map((faq) => (
                      <AccordionItem key={faq.question} question={faq.question} answer={faq.answer} />
                    ))}
                  </div>
                </div>
              )
            })}
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
            ¿No encontrás lo que buscás?
          </h2>
          <p className="text-sm mb-6 max-w-lg mx-auto" style={{ color: '#aaa' }}>
            Escribinos por WhatsApp o visitá nuestra página de contacto. Estamos para ayudarte.
          </p>
          <div className="flex flex-wrap justify-center gap-4">
            <a
              href="https://wa.me/5493516197836"
              target="_blank"
              rel="noopener noreferrer"
              className="inline-block px-6 py-3 text-sm font-semibold uppercase tracking-wider transition-opacity hover:opacity-90"
              style={{ background: 'var(--color-primary)', color: 'var(--color-dark)' }}
            >
              WhatsApp
            </a>
            <Link
              href="/contacto"
              className="inline-block px-6 py-3 text-sm font-semibold uppercase tracking-wider transition-opacity hover:opacity-90"
              style={{ border: '1px solid var(--color-primary)', color: 'var(--color-primary)' }}
            >
              Contacto
            </Link>
          </div>
        </div>
      </section>
    </>
  )
}
