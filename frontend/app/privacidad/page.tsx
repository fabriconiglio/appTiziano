import Link from 'next/link'
import { ChevronRight } from 'lucide-react'

export const metadata = {
  title: 'Política de Privacidad — Tiziano Peluquería',
  description:
    'Política de privacidad y protección de datos personales de Tiziano Peluquería & Spa.',
}

const sections = [
  {
    title: '1. Responsable del tratamiento',
    paragraphs: [
      'El responsable del tratamiento de tus datos personales es **Tiziano Peluquería & Spa**, con domicilio en Santa Ana 2725, Loc. 2, Ciudad de Córdoba, Provincia de Córdoba, Argentina. CUIT: 27-25757470-4.',
      'Contacto para consultas sobre datos personales: tizianopeluqueriaspa@gmail.com.',
    ],
  },
  {
    title: '2. Datos que recopilamos',
    paragraphs: [
      'Recopilamos los siguientes tipos de datos personales:',
      '- **Datos de registro:** nombre completo, dirección de email, contraseña (almacenada de forma cifrada).',
      '- **Datos de compra:** historial de pedidos, productos adquiridos, montos, método de pago utilizado, dirección de entrega.',
      '- **Datos de navegación:** dirección IP, tipo de navegador, páginas visitadas, fecha y hora de acceso. Estos datos se recopilan de forma automatizada con fines estadísticos.',
      '- **Datos de contacto voluntario:** información proporcionada a través de formularios de contacto, WhatsApp o email.',
    ],
  },
  {
    title: '3. Finalidad del tratamiento',
    paragraphs: [
      'Utilizamos tus datos personales para las siguientes finalidades:',
      '- Gestionar tu cuenta de usuario y autenticar tu identidad.',
      '- Procesar y gestionar tus pedidos, incluyendo la coordinación del envío y la comunicación sobre el estado de tu compra.',
      '- Enviar notificaciones transaccionales por email: confirmación de registro, confirmación de compra, cambios de estado del pedido, confirmación de arrepentimiento.',
      '- Responder consultas realizadas a través de los canales de contacto.',
      '- Mejorar nuestro sitio web y la experiencia de compra mediante el análisis de datos de navegación.',
      '- Cumplir con obligaciones legales y fiscales.',
    ],
  },
  {
    title: '4. Base legal del tratamiento',
    paragraphs: [
      'El tratamiento de tus datos se fundamenta en:',
      '- **Consentimiento:** al registrarte y realizar una compra, prestás tu consentimiento para el tratamiento de tus datos con las finalidades descritas.',
      '- **Ejecución contractual:** el procesamiento de datos es necesario para cumplir con el contrato de compraventa.',
      '- **Obligación legal:** determinados datos son tratados para cumplir con obligaciones fiscales y legales vigentes en Argentina.',
    ],
  },
  {
    title: '5. Compartición con terceros',
    paragraphs: [
      'Tus datos personales pueden ser compartidos con los siguientes terceros, exclusivamente para las finalidades indicadas:',
      '- **Taca Taca (Servicio de Pago SAU):** procesamiento de pagos con tarjeta de crédito y débito. Taca Taca opera como Proveedor de Servicios de Pago autorizado por el BCRA.',
      '- **Servicios de mensajería y correo:** para la entrega de los productos adquiridos (Correo Argentino, Andreani u otros operadores logísticos).',
      '- **Google:** si optaste por iniciar sesión con Google, se comparten datos básicos de autenticación con Google LLC.',
      'No vendemos, alquilamos ni cedemos tus datos personales a terceros con fines comerciales o publicitarios.',
    ],
  },
  {
    title: '6. Seguridad de los datos',
    paragraphs: [
      'Implementamos medidas de seguridad técnicas y organizativas para proteger tus datos personales contra el acceso no autorizado, la alteración, la divulgación o la destrucción:',
      '- Conexiones cifradas mediante HTTPS/TLS.',
      '- Contraseñas almacenadas con hashing seguro (bcrypt).',
      '- Tokens de autenticación (API tokens) con expiración automática.',
      '- Acceso restringido a los datos personales únicamente al personal autorizado.',
      '- No almacenamos datos de tarjetas de crédito o débito; estos son gestionados exclusivamente por el procesador de pagos.',
    ],
  },
  {
    title: '7. Derechos del titular de los datos',
    paragraphs: [
      'De acuerdo con la **Ley 25.326 de Protección de Datos Personales** y su decreto reglamentario, tenés derecho a:',
      '- **Acceso:** solicitar información sobre qué datos personales tuyos tenemos almacenados.',
      '- **Rectificación:** solicitar la corrección de datos inexactos o incompletos.',
      '- **Supresión:** solicitar la eliminación de tus datos personales cuando ya no sean necesarios para la finalidad para la que fueron recopilados.',
      '- **Oposición:** oponerte al tratamiento de tus datos en determinadas circunstancias.',
      'Para ejercer estos derechos, enviá un email a tizianopeluqueriaspa@gmail.com con el asunto "Datos Personales", indicando tu nombre completo, email de registro y el derecho que deseás ejercer. Responderemos dentro de los 10 días hábiles.',
      'La **Agencia de Acceso a la Información Pública** (AAIP), en su carácter de Órgano de Control de la Ley 25.326, tiene la atribución de atender las denuncias y reclamos que se interpongan con relación al incumplimiento de las normas sobre protección de datos personales.',
    ],
  },
  {
    title: '8. Cookies',
    paragraphs: [
      'Nuestro sitio web utiliza cookies y tecnologías similares para:',
      '- Mantener tu sesión iniciada mientras navegás.',
      '- Recordar las preferencias de tu carrito de compras.',
      '- Recopilar datos anónimos de uso del sitio con fines estadísticos.',
      'Podés configurar tu navegador para rechazar cookies, aunque esto puede afectar la funcionalidad del sitio.',
    ],
  },
  {
    title: '9. Conservación de los datos',
    paragraphs: [
      'Conservamos tus datos personales durante el tiempo necesario para cumplir con las finalidades descritas y con las obligaciones legales aplicables:',
      '- **Datos de cuenta:** mientras mantengas tu cuenta activa. Podés solicitar la eliminación en cualquier momento.',
      '- **Datos de compra:** durante el plazo legal exigido para la documentación fiscal y comercial (10 años según la legislación argentina).',
      '- **Datos de navegación:** máximo 2 años.',
    ],
  },
  {
    title: '10. Modificaciones a esta política',
    paragraphs: [
      'Tiziano se reserva el derecho de modificar la presente Política de Privacidad en cualquier momento. Las modificaciones serán publicadas en esta misma página con la fecha de actualización.',
      'Te recomendamos revisar esta página periódicamente para estar informado sobre cómo protegemos tu información.',
      '**Última actualización:** Abril 2026.',
    ],
  },
  {
    title: '11. Contacto',
    paragraphs: [
      'Si tenés preguntas o inquietudes sobre esta Política de Privacidad o sobre el tratamiento de tus datos personales, podés contactarnos:',
      '- **Email:** tizianopeluqueriaspa@gmail.com',
      '- **WhatsApp:** (0351) 619-7836',
      '- **Dirección:** Santa Ana 2725, Loc. 2, Córdoba, Argentina',
    ],
  },
]

function renderLine(line: string, key: number) {
  const elements: React.ReactNode[] = []
  const regex = /\*\*([^*]+)\*\*/g
  let lastIndex = 0
  let match
  let partKey = 0

  while ((match = regex.exec(line)) !== null) {
    if (match.index > lastIndex) {
      elements.push(line.slice(lastIndex, match.index))
    }
    elements.push(
      <strong key={`${key}-${partKey++}`} style={{ color: 'var(--color-dark)' }}>
        {match[1]}
      </strong>
    )
    lastIndex = match.index + match[0].length
  }

  if (lastIndex < line.length) {
    elements.push(line.slice(lastIndex))
  }

  const isBullet = line.startsWith('- ')

  return (
    <p key={key} className={`mb-2 ${isBullet ? 'pl-4' : ''}`}>
      {elements}
    </p>
  )
}

export default function PrivacidadPage() {
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
            <span style={{ color: 'var(--color-primary)' }}>Política de Privacidad</span>
          </nav>
          <h1
            style={{
              fontFamily: 'var(--font-display)',
              fontSize: 'clamp(2rem, 5vw, 3rem)',
              color: 'var(--color-white)',
              fontStyle: 'italic',
            }}
          >
            Política de Privacidad
          </h1>
          <p
            className="mt-2 text-sm max-w-xl"
            style={{ color: 'var(--color-primary-light)', fontStyle: 'italic' }}
          >
            Cómo recopilamos, usamos y protegemos tu información personal.
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
                  className="text-sm leading-relaxed"
                  style={{ color: 'var(--color-dark-soft)' }}
                >
                  {section.paragraphs.map((p, idx) => renderLine(p, idx))}
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>
    </>
  )
}
