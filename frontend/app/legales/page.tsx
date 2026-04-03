import Link from 'next/link'
import { ChevronRight } from 'lucide-react'

export const metadata = {
  title: 'Términos y Condiciones — Tiziano Peluquería',
  description:
    'Términos y condiciones de uso de la tienda online de Tiziano Peluquería & Spa.',
}

const sections = [
  {
    title: '1. Datos del proveedor',
    content: `El presente sitio web y tienda online es operado por **Tiziano Peluquería & Spa** (en adelante, "Tiziano"), con domicilio en Santa Ana 2725, Loc. 2, Ciudad de Córdoba, Provincia de Córdoba, Argentina. CUIT: 27-25757470-4.

Contacto: tizianopeluqueriaspa@gmail.com | WhatsApp: (0351) 619-7836.`,
  },
  {
    title: '2. Objeto',
    content: `Tiziano ofrece a través de su tienda online la venta minorista y mayorista de productos capilares profesionales, incluyendo shampoos, acondicionadores, tratamientos, coloración, styling y accesorios de marcas reconocidas.

El uso del sitio y la realización de compras implican la aceptación plena de los presentes Términos y Condiciones.`,
  },
  {
    title: '3. Condiciones de compra',
    content: `- Para realizar una compra es necesario crear una cuenta de usuario.
- Los precios publicados están expresados en **Pesos Argentinos (ARS)** y **no incluyen impuestos**, conforme a lo establecido por la **Ley N° 27.743, Art. 39**.
- Tiziano se reserva el derecho de modificar los precios sin previo aviso, sin que ello afecte a los pedidos ya confirmados.
- La disponibilidad de los productos está sujeta al stock existente. En caso de faltante, nos comunicaremos para ofrecer alternativas o proceder al reembolso.
- La confirmación del pedido se realiza una vez verificado el pago. Recibirás un email con el detalle y número de seguimiento de tu orden.`,
  },
  {
    title: '4. Medios de pago',
    content: `Aceptamos los siguientes medios de pago:

- **Tarjetas de crédito y débito** (Visa, Mastercard, American Express) procesadas a través de la plataforma Taca Taca, proveedor de servicios de pago autorizado por el Banco Central de la República Argentina.
- **Transferencia bancaria** a la cuenta indicada al momento de confirmar el pedido. El pedido se confirma una vez acreditado el pago y verificado el comprobante.

Los datos de pago son gestionados exclusivamente por los procesadores de pago correspondientes. Tiziano no almacena datos de tarjetas de crédito o débito.`,
  },
  {
    title: '5. Envíos y entrega',
    content: `Realizamos envíos a todo el territorio argentino y ofrecemos la opción de retiro gratuito en nuestro local.

Los plazos de entrega son estimados y pueden variar según la localidad de destino y la disponibilidad del servicio de correo. Para más información, consultá nuestra [página de Métodos de Envío](/envios).

El riesgo de la mercadería se transfiere al comprador en el momento de la entrega.`,
  },
  {
    title: '6. Derecho de arrepentimiento',
    content: `De conformidad con el **Art. 34 de la Ley 24.240 de Defensa del Consumidor**, el consumidor tiene derecho a revocar la aceptación de la compra dentro de los **10 (diez) días corridos** contados a partir de la fecha de entrega del bien o de la celebración del contrato, lo último que ocurra, sin responsabilidad alguna y sin necesidad de indicar el motivo.

Para ejercer este derecho, utilizá el [Botón de Arrepentimiento](/arrepentimiento) disponible en el pie de página de nuestro sitio. No es necesario estar registrado.

El reembolso se realizará dentro de los 10 días hábiles posteriores a la recepción del producto devuelto, utilizando el mismo medio de pago original. Los costos de envío de la devolución corren por cuenta del consumidor, salvo que el producto presente defectos.`,
  },
  {
    title: '7. Garantías',
    content: `Todos los productos comercializados por Tiziano cuentan con la garantía legal establecida por la **Ley 24.240 de Defensa del Consumidor**.

En caso de recibir un producto defectuoso o en mal estado, contactanos dentro de las 48 horas de recibido a tizianopeluqueriaspa@gmail.com o por WhatsApp, adjuntando fotos del producto y el embalaje. Procederemos al reemplazo o reembolso según corresponda.

Por razones de higiene, no se aceptan devoluciones de productos que hayan sido abiertos o utilizados, salvo defectos de fabricación.`,
  },
  {
    title: '8. Propiedad intelectual',
    content: `Todo el contenido del sitio web (textos, imágenes, diseño, logotipos, marcas) es propiedad de Tiziano o de sus respectivos titulares y está protegido por las leyes de propiedad intelectual vigentes en la República Argentina.

Queda prohibida la reproducción, distribución o uso no autorizado del contenido del sitio sin consentimiento previo por escrito.`,
  },
  {
    title: '9. Limitación de responsabilidad',
    content: `Tiziano no será responsable por daños indirectos, incidentales o consecuentes derivados del uso del sitio web o de los productos adquiridos, salvo lo expresamente previsto por la legislación vigente.

Tiziano no garantiza la disponibilidad ininterrumpida del sitio web y se reserva el derecho de suspender temporal o definitivamente el servicio por razones técnicas o de mantenimiento.`,
  },
  {
    title: '10. Ley aplicable y jurisdicción',
    content: `Los presentes Términos y Condiciones se rigen por las leyes de la República Argentina. Cualquier controversia derivada del uso del sitio o de las compras realizadas será sometida a la jurisdicción de los **Tribunales Ordinarios de la Ciudad de Córdoba**, Provincia de Córdoba, renunciando las partes a cualquier otro fuero o jurisdicción que pudiera corresponderles.

El consumidor también podrá realizar reclamos ante la **Dirección General de Defensa del Consumidor** de la Provincia de Córdoba.`,
  },
  {
    title: '11. Modificaciones',
    content: `Tiziano se reserva el derecho de modificar los presentes Términos y Condiciones en cualquier momento. Las modificaciones entrarán en vigencia a partir de su publicación en el sitio web. El uso continuado del sitio después de dichas modificaciones constituye la aceptación de los nuevos términos.

**Última actualización:** Abril 2026.`,
  },
]

function renderContent(content: string) {
  const elements: React.ReactNode[] = []

  let i = 0
  const raw = content
  let lastIndex = 0

  const regex = /(\*\*([^*]+)\*\*|\[([^\]]+)\]\(([^)]+)\))/g
  let match

  while ((match = regex.exec(raw)) !== null) {
    if (match.index > lastIndex) {
      elements.push(raw.slice(lastIndex, match.index))
    }
    if (match[0].startsWith('**')) {
      elements.push(
        <strong key={i++} style={{ color: 'var(--color-dark)' }}>
          {match[2]}
        </strong>
      )
    } else if (match[0].startsWith('[')) {
      elements.push(
        <Link key={i++} href={match[4]} style={{ color: 'var(--color-primary-dark)', textDecoration: 'underline' }}>
          {match[3]}
        </Link>
      )
    }
    lastIndex = match.index + match[0].length
  }

  if (lastIndex < raw.length) {
    elements.push(raw.slice(lastIndex))
  }

  return elements
}

export default function LegalesPage() {
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
            <span style={{ color: 'var(--color-primary)' }}>Términos y Condiciones</span>
          </nav>
          <h1
            style={{
              fontFamily: 'var(--font-display)',
              fontSize: 'clamp(2rem, 5vw, 3rem)',
              color: 'var(--color-white)',
              fontStyle: 'italic',
            }}
          >
            Términos y Condiciones
          </h1>
          <p
            className="mt-2 text-sm max-w-xl"
            style={{ color: 'var(--color-primary-light)', fontStyle: 'italic' }}
          >
            Condiciones generales de uso del sitio web y la tienda online.
          </p>
        </div>
      </div>

      <section style={{ background: 'var(--color-bg)', padding: '64px 0' }}>
        <div className="max-w-3xl mx-auto px-6">
          <div className="flex flex-col gap-8">
            {sections.map((section) => (
              <div key={section.title}>
                <h2
                  className="text-base font-bold mb-3"
                  style={{ color: 'var(--color-dark)' }}
                >
                  {section.title}
                </h2>
                <div
                  className="text-sm leading-relaxed whitespace-pre-line"
                  style={{ color: 'var(--color-dark-soft)' }}
                >
                  {section.content.split('\n').map((line) => (
                    <p key={line} className={line.trim() === '' ? 'h-3' : 'mb-2'}>
                      {line.trim() === '' ? null : renderContent(line)}
                    </p>
                  ))}
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>
    </>
  )
}
