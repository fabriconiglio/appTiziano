import { Quote } from 'lucide-react'

const testimonials = [
  {
    name: 'Valentina M.',
    role: 'Estilista Profesional',
    text: 'Los productos de Tiziano transformaron mi salón. La calidad es incomparable y mis clientes lo notan desde la primera aplicación.',
    initials: 'VM',
  },
  {
    name: 'Romina García',
    role: 'Colorista Certificada',
    text: 'La línea de coloración es extraordinaria. Los tonos son vibrantes, duran mucho y el cabello queda suave y brillante.',
    initials: 'RG',
  },
  {
    name: 'Santiago Torres',
    role: 'Director de Peluquería',
    text: 'Profesionalismo y calidad en cada producto. La atención al cliente es excelente y los envíos siempre llegan a tiempo.',
    initials: 'ST',
  },
]

export default function TestimonialsSection() {
  return (
    <section style={{ background: 'var(--color-cream)', padding: '80px 0' }}>
      <div className="max-w-7xl mx-auto px-6">
        <div className="text-center mb-14">
          <p
            className="text-xs uppercase tracking-widest font-semibold mb-3"
            style={{ color: 'var(--color-primary)' }}
          >
            Experiencias reales
          </p>
          <h2 className="section-title">Lo que dicen los profesionales</h2>
          <div className="decorative-line mt-4" />
        </div>

        <div className="grid md:grid-cols-3 gap-6">
          {testimonials.map((t) => (
            <div
              key={t.name}
              className="flex flex-col p-8"
              style={{
                background: 'var(--color-white)',
                border: '1px solid var(--color-border)',
              }}
            >
              <Quote
                size={32}
                className="mb-5 opacity-30"
                style={{ color: 'var(--color-primary)' }}
              />
              <p
                className="text-sm leading-relaxed flex-1 mb-6 italic"
                style={{ color: 'var(--color-dark-soft)' }}
              >
                "{t.text}"
              </p>
              <div className="flex items-center gap-3">
                <div
                  className="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold text-white shrink-0"
                  style={{ background: 'var(--color-primary)' }}
                >
                  {t.initials}
                </div>
                <div>
                  <p
                    className="font-semibold text-sm"
                    style={{ color: 'var(--color-dark)', fontFamily: 'var(--font-display)' }}
                  >
                    {t.name}
                  </p>
                  <p
                    className="text-xs"
                    style={{ color: 'var(--color-primary-dark)', fontStyle: 'italic' }}
                  >
                    {t.role}
                  </p>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}
