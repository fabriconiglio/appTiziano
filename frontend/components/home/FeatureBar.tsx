import { Truck, ShieldCheck, Headphones, Award } from 'lucide-react'

const features = [
  {
    Icon: Truck,
    title: 'Envío a Todo el País',
    desc: 'Despacho en 24-48 hs hábiles',
  },
  {
    Icon: ShieldCheck,
    title: 'Productos Originales',
    desc: '100% auténticos con garantía',
  },
  {
    Icon: Award,
    title: 'Calidad Profesional',
    desc: 'Líneas de uso profesional',
  },
  {
    Icon: Headphones,
    title: 'Atención Personalizada',
    desc: 'Asesoramiento de expertos',
  },
]

export default function FeatureBar() {
  return (
    <section
      style={{
        background: 'var(--color-dark)',
        padding: '40px 0',
        borderBottom: '3px solid var(--color-primary)',
      }}
    >
      <div className="max-w-7xl mx-auto px-6 grid grid-cols-2 lg:grid-cols-4 gap-8">
        {features.map(({ Icon, title, desc }) => (
          <div key={title} className="flex items-center gap-4">
            <div
              className="w-11 h-11 rounded-full flex items-center justify-center shrink-0"
              style={{ border: '1px solid var(--color-primary)', color: 'var(--color-primary)' }}
            >
              <Icon size={20} />
            </div>
            <div>
              <p
                className="font-semibold text-sm"
                style={{ color: 'var(--color-white)' }}
              >
                {title}
              </p>
              <p className="text-xs mt-0.5" style={{ color: '#aaa' }}>
                {desc}
              </p>
            </div>
          </div>
        ))}
      </div>
    </section>
  )
}
